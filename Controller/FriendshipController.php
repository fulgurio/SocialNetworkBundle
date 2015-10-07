<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fulgurio\SocialNetworkBundle\Controller;

use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Entity\UserFriendship;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Friendship controller
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class FriendshipController extends Controller
{
    /**
     * Friend user list page
     */
    public function listAction()
    {
        $request = $this->get('request');
        $currentUser = $this->getUser();
        $page = $request->query->get('page', 1);
        $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
        $friendshipRepository = $this->getDoctrine()->getRepository($userFriendshipClassName);
        $askingFriends = $friendshipRepository->findAskingFriends($currentUser);
        $friends = $friendshipRepository->findAcceptedAndPendingFriends($currentUser, $page, $this->get('knp_paginator'));
        return $this->render('FulgurioSocialNetworkBundle:Friendship:list.html.twig', array(
            'friendsAsking' => $askingFriends,
            'friends' => $friends
        ));
    }

    /**
     * Search to add new friend action
     */
    public function searchToAddAction()
    {
        $request = $this->get('request');
        $pendingFriendshipsIDs = array();
        $users = NULL;
        $searchValue = $request->get('search');
        if (trim($searchValue) != '')
        {
            $currentUser = $this->getUser();
            $userClassName = $this->container->getParameter('fos_user.model.user.class');
            $userRepository = $this->getDoctrine()->getRepository($userClassName);
            $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
            $friendshipRepository = $this->getDoctrine()->getRepository($userFriendshipClassName);
            $excludeIDs = array($currentUser->getId());
            $friendships = $friendshipRepository->findAcceptedAndRefusedFriends($currentUser);
            foreach ($friendships as $friendship)
            {
                $excludeIDs[] = $friendship['id'];
            }
            $users = $userRepository->findOnlyInEnabledSubscribers($searchValue, $excludeIDs);
            $pendingFriendships = $friendshipRepository->findPendingFriends($currentUser);
            foreach ($pendingFriendships as $pendingFriendship)
            {
                $id = $pendingFriendship['id'];
                $pendingFriendshipsIDs[$id] = $id;
            }
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Friendship:add.html.twig',
                array(
                    'searchValue' => $searchValue,
                    'users' => $users,
                    'pendingFriendshipsIDs' => $pendingFriendshipsIDs,
                )
        );
    }

    /**
     * Friend user add page
     *
     * @param Request $request
     */
    public function addAction(Request $request)
    {
        $selectedFriends = $request->get('userId')
                ? array($request->get('userId'))
                : $request->get('friends_id');
        if ($selectedFriends)
        {
            $em = $this->getDoctrine()->getManager();
            foreach($selectedFriends as $selectedFriendId)
            {
                $mayBeFriend = $this->getSpecifiedUser($selectedFriendId);
                if ($this->addSingleFriend($mayBeFriend))
                {
                    $this->get('fulgurio_social_network.friendship_mailer')->sendInvitMessage($mayBeFriend);
                }
            }
            $em->flush();
            $this->addTransFlash('notice', 'fulgurio.socialnetwork.invitation.confirm.notice');
        }
        return $this->redirect($this->generateUrl('fulgurio_social_network_friendship_list'));
    }

    /**
     * Ask a user to be friend
     *
     * @param User $mayBeFriend
     * @return boolean
     */
    protected function addSingleFriend(User $mayBeFriend)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $nbRefusals = $this->container->getParameter('fulgurio_social_network.friendship.nb_refusals');
        $usersFriendships = $this->getFriendships($currentUser, $mayBeFriend);
        if ($usersFriendships)
        {
            if ($usersFriendships[0]->getUserSrc() == $currentUser)
            {
                if ($usersFriendships[0]->getNbRefusals() >= $nbRefusals)
                {
                    return FALSE;
                }
                $friendship = $usersFriendships[0];
                $friendship2 = $usersFriendships[1];
            }
            else
            {
                if ($usersFriendships[1]->getNbRefusals() >= $nbRefusals)
                {
                    return FALSE;
                }
                $friendship = $usersFriendships[1];
                $friendship2 = $usersFriendships[0];
            }
        }
        else
        {
            $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
            $friendship = new $userFriendshipClassName();
            $friendship->setUserSrc($currentUser);
            $friendship->setUserTgt($mayBeFriend);
            $friendship2 = new $userFriendshipClassName();
            $friendship2->setUserSrc($mayBeFriend);
            $friendship2->setUserTgt($currentUser);
        }
        $friendship->setStatus(UserFriendship::PENDING_STATUS);
        $friendship2->setStatus(UserFriendship::ASKING_STATUS);
        $em->persist($friendship);
        $em->persist($friendship2);
        return TRUE;
    }

    /**
     * Friend user invit page
     */
    public function invitAction($userId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $user = $this->getSpecifiedUser($userId);
        $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
        if (!$user->hasRole('ROLE_ADMIN')
          && !$user->hasRole('ROLE_SUPER_ADMIN')
          && !$user->hasRole('ROLE_GHOST')
          && !$this->getDoctrine()->getRepository($userFriendshipClassName)->areFriends($currentUser, $user)
        )
        {
            $friendship = new $userFriendshipClassName();
            $friendship->setUserSrc($currentUser);
            $friendship->setUserTgt($user);
            $friendship->setStatus(UserFriendship::PENDING_STATUS);
            $em->persist($friendship);
            $friendship2 = new $userFriendshipClassName();
            $friendship2->setUserSrc($user);
            $friendship2->setUserTgt($currentUser);
            $friendship2->setStatus(UserFriendship::ASKING_STATUS);
            $em->persist($friendship2);
            $em->flush();
        }
        if ($request->headers->get('referer') != '')
        {
            return $this->redirect($request->headers->get('referer'));
        }
        else
        {
            return $this->redirect($this->generateUrl('fulgurio_social_network_friendship_list'));
        }
    }

    /**
     * Accept invitation action
     *
     * @param integer $userId
     * @throws NotFoundHttpException
     */
    public function acceptAction($userId)
    {
        $currentUser = $this->getUser();
        $user = $this->getSpecifiedUser($userId);
        $friendships = $this->getFriendships($currentUser, $user);
        if ($friendships == FALSE)
        {
            throw new NotFoundHttpException();
        }
        foreach ($friendships as $friendship)
        {
            $friendship->setNbRefusals(0);
            $friendship->setStatus(UserFriendship::ACCEPTED_STATUS);
        }
        $this->get('fulgurio_social_network.friendship_mailer')->sendAcceptMessage($user);
        $this->addTransFlash(
                'notice',
                'fulgurio.socialnetwork.accept.confirm.notice',
                array('%USERNAME%' => $user)
        );
        $redirectUrl = $this->generateUrl('fulgurio_social_network_friendship_list');
        if ($this->getRequest()->isXmlHttpRequest())
        {
            return new JsonResponse(
                    array('success' => 1, 'redirect' => $redirectUrl));
        }
        return $this->redirect($redirectUrl);
    }

    /**
     * Refuse invitation action
     *
     * @param integer $userId
     * @throws NotFoundHttpException
     * @todo Send an email
     */
    public function refuseAction($userId)
    {
        $request = $this->get('request');
        $user = $this->getSpecifiedUser($userId);
        $currentUser = $this->getUser();
        $usersFriendships = $this->getFriendships($currentUser, $user);
        if ($usersFriendships == FALSE)
        {
            throw new NotFoundHttpException();
        }
        if ($request->get('confirm'))
        {
            if ($request->get('confirm') === 'yes')
            {
                $em = $this->getDoctrine()->getManager();
                $hasAcceptedBefore = $this->updateStatusToRefusal($usersFriendships);
                if ($hasAcceptedBefore)
                {
                    $message = 'fulgurio.socialnetwork.remove.confirm.notice';
                    $this->get('fulgurio_social_network.friendship_mailer')->sendRemoveInvitMessage($user);
                }
                else
                {
                    $message = 'fulgurio.socialnetwork.refuse.confirm.notice';
                    $this->get('fulgurio_social_network.friendship_mailer')->sendRefusalMessage($user);
                }
                $em->flush();
                $this->addTransFlash('notice', $message, array('%USERNAME%' => $user));
            }
            $redirectUrl = $this->generateUrl('fulgurio_social_network_friendship_list');
            if ($request->isXmlHttpRequest())
            {
                return new JsonResponse(array(
                    'success' => 1,
                    'redirect' => $redirectUrl
                ));
            }
            return $this->redirect($redirectUrl);
        }
        $templateName = 'FulgurioSocialNetworkBundle::confirm' . ($request->isXmlHttpRequest() ? 'Ajax' : '') . '.html.twig';
        return $this->render($templateName, array(
            'action' => $this->generateUrl('fulgurio_social_network_friendship_refuse', array('userId' => $userId)),
            'title' => $this->translate('fulgurio.socialnetwork.refuse.confirm.title'),
            'confirmationMessage' => $this->translate('fulgurio.socialnetwork.refuse.confirm.message', array('%USERNAME%' => $user))
        ));
    }

    /**
     * Update friendship to refusal or removed status
     *
     * @param Collection $usersFriendships
     * @return $boolean
     */
    private function updateStatusToRefusal($usersFriendships)
    {
        $hasAcceptedBefore = FALSE;
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $nbRefusalsLimit = $this->container->getParameter('fulgurio_social_network.friendship.nb_refusals');
        foreach ($usersFriendships as $userFriendship)
        {
            if ($userFriendship->getStatus() == UserFriendship::ACCEPTED_STATUS)
            {
                $hasAcceptedBefore = TRUE;
            }
            $userFriendship->setStatus(UserFriendship::REFUSED_STATUS);
            if ($userFriendship->getUserTgt()->getId() == $currentUser->getId())
            {
                $nbRefusals = $userFriendship->getNbRefusals();
                if ($nbRefusals >= $nbRefusalsLimit)
                {
                    $userFriendship->setStatus(UserFriendship::REMOVED_STATUS);
                }
                else
                {
                    $userFriendship->setNbRefusals($nbRefusals + 1);
                }
            }
            $em->persist($userFriendship);
        }
        return $hasAcceptedBefore;
    }

    /**
     * Search friend with ajax call
     *
     * @param Request $request
     * @throws AccessDeniedException
     */
    public function searchAction(Request $request)
    {
        if ($request->isXmlHttpRequest())
        {
            $userClassName = $this->container->getParameter('fos_user.model.user.class');
            $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
            $foundedFriends = $this->getDoctrine()
                    ->getRepository($userFriendshipClassName)
                    ->searchFriend(
                            $this->getUser(),
                            $request->get('q')
            );
            $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            foreach ($foundedFriends as &$friend)
            {
                $friend['avatar'] = $helper->asset($friend, 'avatarFile', $userClassName);
            }
            return new JsonResponse($foundedFriends);
        }
        throw new AccessDeniedException();
    }

    /**
     * Get user from given ID, and ckeck if he exists
     *
     * @throws NotFoundHttpException
     * @param number $userId
     * @return User
     */
    private function getSpecifiedUser($userId)
    {
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        if (!$user = $this->getDoctrine()->getRepository($userClassName)->find($userId))
        {
            throw new NotFoundHttpException();
        }
        return $user;
    }

    /**
     * Get friendship between two users
     *
     * @param string $user1
     * @param string $user2
     * @return UserFriendship
     * @throws NotFoundHttpException
     */
    private function getFriendships($user1, $user2)
    {
        $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
        $friendshipRepository = $this->getDoctrine()->getRepository($userFriendshipClassName);
        $friendship = $friendshipRepository->findByUserAndFriendUser($user1, $user2);
        return $friendship;
    }

    /**
     * Helper to add a translated flash
     *
     * @param string $type
     * @param string $message
     * @param array $data
     */
    private function addTransFlash($type, $message, $data = array())
    {
        $this->get('session')->getFlashBag()->add(
                $type,
                $this->translate($message, $data)
        );
    }

    /**
     * Translator helper
     *
     * @param string $message
     * @param array $data
     * @return string
     */
    private function translate($message, $data = array())
    {
        return $this->get('translator')->trans($message, $data, 'friendship');
    }
}