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

use Fulgurio\SocialNetworkBundle\Entity\UserFriendship;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $friendshipRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:UserFriendship');
        return $this->render('FulgurioSocialNetworkBundle:Friendship:list.html.twig', array(
            'friendsAsking' => $friendshipRepository->findAskingFriends($currentUser),
            'friends' => $friendshipRepository->findAcceptedAndPendingFriends($currentUser, $page, $this->get('knp_paginator')),
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
            $userRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User');
            $friendshipRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:UserFriendship');
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
        return $this->render('FulgurioSocialNetworkBundle:Friendship:add.html.twig', array(
                'searchValue' => $searchValue,
                'users' => $users,
                'pendingFriendshipsIDs' => $pendingFriendshipsIDs,
        ));
    }

    /**
     * Friend user add page
     */
    public function addAction(Request $request)
    {
        if ($selectedFriends = $request->get('friends_id'))
        {
            $currentUser = $this->getUser();
            $userRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User');
            $friendshipRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:UserFriendship');
            $em = $this->getDoctrine()->getManager();
            foreach($selectedFriends as $selectedFriendId)
            {
                $mayBeFriend = $userRepository->findOneById($selectedFriendId);
                if ($usersFriendship = $friendshipRepository->findByUserAndFriendUser($currentUser, $mayBeFriend))
                {
                    if ($usersFriendship[0]->getUserSrc() == $currentUser)
                    {
                        if ($usersFriendship[0]->getNbRefusals() >= $this->container->getParameter('fulgurio_social_network.friendship_nb_refusals'))
                        {
                            continue;
                        }
                        $friendship = $usersFriendship[0];
                        $friendship2 = $usersFriendship[1];
                    }
                    else
                    {
                        if ($usersFriendship[1]->getNbRefusals() >= $this->container->getParameter('fulgurio_social_network.friendship_nb_refusals'))
                        {
                            continue;
                        }
                        $friendship = $usersFriendship[1];
                        $friendship2 = $usersFriendship[0];
                    }
                }
                else
                {
                    $friendship = new UserFriendship();
                    $friendship->setUserSrc($currentUser);
                    $friendship->setUserTgt($mayBeFriend);
                    $friendship2 = new UserFriendship();
                    $friendship2->setUserSrc($mayBeFriend);
                    $friendship2->setUserTgt($currentUser);
                }
                $friendship->setStatus('pending');
                $friendship2->setStatus('asking');
                $em->persist($friendship);
                $em->persist($friendship2);
                $this->get('fulgurio_social_network.friendship_mailer')->sendInvitMessage($mayBeFriend);
            }
            $em->persist($currentUser);
            $em->flush();
            $this->get('session')->getFlashBag('notice',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.invitation.success_msg',
                            array(),
                            'friendship'
            ));
        }
        return $this->redirect($this->generateUrl('fulgurio_social_network_friendship_list'));
    }

    /**
     * Friend user invit page
     */
    public function invitAction($userId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $user = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User')->find($userId);
        if (!$user->hasRole('ROLE_ADMIN')
          && !$user->hasRole('ROLE_SUPER_ADMIN')
          && !$user->hasRole('ROLE_GHOST')
          && !$this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:UserFriendship')->areFriends($currentUser, $user)
        )
        {
            $friendship = new UserFriendship();
            $friendship->setUserSrc($currentUser);
            $friendship->setUserTgt($user);
            $friendship->setStatus('pending');
            $em->persist($friendship);
            $friendship2 = new UserFriendship();
            $friendship2->setUserSrc($user);
            $friendship2->setUserTgt($currentUser);
            $friendship2->setStatus('asking');
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
        $userRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User');
        if (!$user = $userRepository->find($userId))
        {
            throw new NotFoundHttpException();
        }
        $friendshipRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:UserFriendship');
        if (!$usersFriendship = $friendshipRepository->findByUserAndFriendUser($currentUser, $user))
        {
            throw new NotFoundHttpException();
        }
        $em = $this->getDoctrine()->getManager();
        foreach ($usersFriendship as $userFriendship)
        {
            $userFriendship->setNbRefusals(0);
            $userFriendship->setStatus('accepted');
            $em->persist($userFriendship);
        }
        $em->flush();

        $this->get('fulgurio_social_network.friendship_mailer')->sendAcceptMessage($user);
            $this->get('session')->getFlashBag('notice',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.add.accepted_msg',
                            array('%username%' => $user->getUsername()),
                            'friendship'
            ));
        return $this->redirect($this->generateUrl('fulgurio_social_network_friendship_list'));
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
        $currentUser = $this->getUser();
        $userRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User');
        if (!$user = $userRepository->find($userId))
        {
            throw new NotFoundHttpException();
        }
        $friendshipRepository = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:UserFriendship');
        if (!$usersFriendship = $friendshipRepository->findByUserAndFriendUser($currentUser, $user))
        {
            throw new NotFoundHttpException();
        }
        $hasAcceptedBefore = FALSE;
        $em = $this->getDoctrine()->getManager();
        foreach ($usersFriendship as $userFriendship)
        {
            if ($userFriendship->getStatus() == 'accepted')
            {
                $hasAcceptedBefore = TRUE;
            }
            $userFriendship->setStatus('refused');
            if ($userFriendship->getUserTgt() == $currentUser)
            {
                $nbRefusals = $userFriendship->getNbRefusals();
                if ($nbRefusals >= $this->container->getParameter('fulgurio_social_network.friendship_nb_refusals'))
                {
                    $userFriendship->setStatus('removed');
                }
                else
                {
                    $userFriendship->setNbRefusals($userFriendship->getNbRefusals() + 1);
                }
            }
            $em->persist($userFriendship);
        }
        if ($request->get('confirm') === 'yes')
        {
            if ($hasAcceptedBefore)
            {
                $message = 'fulgurio.socialnetwork.add.remove_msg';
                $this->get('fulgurio_social_network.friendship_mailer')->sendRemoveInvitMessage($user);
            }
            else
            {
                $message = 'fulgurio.socialnetwork.add.refused_msg';
                $this->get('fulgurio_social_network.friendship_mailer')->sendRefusalMessage($user);
            }
            $em->flush();
            $this->get('session')->getFlashBag('notice',
                    $this->get('translator')->trans(
                            $message,
                            array('%username%' => $user->getUsername()),
                            'friendship'
            ));
            return $this->redirect($this->generateUrl('fulgurio_social_network_friendship_list'));
        }
        else if ($request->get('confirm') === 'no')
        {
            return $this->redirect($this->generateUrl('fulgurio_social_network_friendship_list'));
        }
        $templateName = $request->isXmlHttpRequest() ? 'FulgurioSocialNetworkBundle::confirmAjax.html.twig' : 'FulgurioSocialNetworkBundle::confirm.html.twig';
        return $this->render($templateName, array(
            'action' => $this->generateUrl('fulgurio_social_network_friendship_refuse', array('userId' => $userId)),
            'confirmationMessage' => $this->get('translator')->trans(
                    $hasAcceptedBefore ? 'fulgurio.socialnetwork.add.confirm_remove_msg' : 'fulgurio.socialnetwork.add.confirm_refuse_msg',
                    array(),
                    'friendship'
            )
        ));
    }
}