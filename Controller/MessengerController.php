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

use Fulgurio\SocialNetworkBundle\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MessengerController extends Controller
{
    const NB_PER_PAGE = 10;

    /**
     * Messenger list page
     *
     * @return Response
     */
    public function listAction()
    {
        if (FALSE == $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            throw new AccessDeniedHttpException();
        }
        $page = $this->getRequest()->query->get('page', 1);
        $query = $this->getMessageRepository()
                ->getRootMessagesQuery($this->getUser());
        $messages = $this->get('knp_paginator')->paginate(
                $query,
                $page,
                self::NB_PER_PAGE
        );
        return $this->render(
                'FulgurioSocialNetworkBundle:Messenger:list.html.twig',
                array('messages' => $messages)
        );
    }

    /**
     * New message page
     *
     * @param number $userId
     * @return Response
     */
    public function newAction($userId = NULL)
    {
        if (FALSE == $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            throw new AccessDeniedHttpException();
        }
        $request = $this->get('request');
        $currentUser = $this->getUser();
        $targetUser = NULL;

        $form = $this->container->get('fulgurio_social_network.messenger.message.new.form');
        if ($userId != NULL)
        {
            $user = $this->getSpecifiedUser($userId);
            $allowContact = $this->getDoctrine()
                    ->getRepository($this->container->getParameter('fos_user.model.user.class'))
                    ->allowContactThemself($currentUser, $user);
            if ($allowContact)
            {
                $form->get('id_targets')->setData(array($user));
                $targetUser = $this->getSpecifiedUser($userId);
            }
        }
        $formHandler = $this->container->get('fulgurio_social_network.messenger.message.new.form.handler');
        if ($formHandler->process(
                $this->getDoctrine(),
                $this->container->get('fulgurio_social_network.messenger_mailer'),
                $currentUser,
                $this->container->getParameter('fulgurio_social_network.messenger.message_target.class')))
        {
            $this->addTransFlash('success', 'fulgurio.socialnetwork.new_message.success_msg');
            $redirectUrl = $this->generateUrl('fulgurio_social_network_messenger_list');
            if ($request->isXmlHttpRequest())
            {
                return new JsonResponse(array(
                    'success' => 1,
                    'redirect' => $redirectUrl
                ));
            }
            return $this->redirect($redirectUrl);
        }
        elseif ($request->isXmlHttpRequest() && $formHandler->hasError())
        {
            return new JsonResponse(array('errors' => $formHandler->getErrors()));
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Messenger:new.html.twig',
                array(
                    'form' => $form->createView(),
                    'targetUser' => $targetUser
                )
        );
    }

    /**
     * Messenger reply page
     *
     * @param number $msgId
     * @return Response
     */
    public function showAction($msgId)
    {
        if (FALSE == $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            throw new AccessDeniedHttpException();
        }
        $message = $this->getMessage($msgId, TRUE);
        $data = array('message' => $message);
        $data['participants'] = $this->getDoctrine()
                ->getRepository($this->container->getParameter('fos_user.model.user.class'))
                ->findChatParticipants($message);
        if ($message->getAllowAnswer())
        {
            $form = $this->getAnswerMessageForm($message, $data['participants']);
            if ($form instanceof Response)
            {
                return $form;
            }
            $data['form'] = $form->createView();
        }
        $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
        $tmpFriends = $this->getDoctrine()
                ->getRepository($userFriendshipClassName)
                ->findAcceptedFriends($this->getUser());
        $data['friends'] = array();
        foreach ($tmpFriends as $tmpFriend)
        {
            $data['friends'][$tmpFriend['id']] = $tmpFriend;
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Messenger:show.html.twig',
                $data
        );
    }

    /**
     * Get answer form
     *
     * @param Message $message
     * @param array $participants
     * @return Form
     */
    private function getAnswerMessageForm(Message $message, $participants)
    {
        $form = $this->get('fulgurio_social_network.messenger.message.answer.form');
        $formHandler = $this->get('fulgurio_social_network.messenger.message.answer.form.handler');
        if ($formHandler->process(
                $this->getDoctrine(),
                $this->container->get('fulgurio_social_network.messenger_mailer'),
                $this->getUser(),
                $message,
                $participants))
        {
            $this->addTransFlash('success', 'fulgurio.socialnetwork.answer_message.success_msg');
            return $this->redirect(
                    $this->generateUrl(
                            'fulgurio_social_network_messenger_show_message',
                            array('msgId' => $message->getId())) . '#comment-' . $form->getData()->getId()
            );
        }
        return $form;
    }

    /**
     * Messenger remove page
     *
     * @param number $msgId
     * @return Response
     */
    public function removeAction($msgId)
    {
        if (FALSE == $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            throw new AccessDeniedHttpException();
        }
        $request = $this->getRequest();
        $currentUser = $this->getUser();
        $message = $this->getMessage($msgId);
        if ($request->request->get('confirm'))
        {
            if ($request->request->get('confirm') === 'yes')
            {
                $this->getMessageRepository()->removeUserMessage($message, $currentUser);
                $this->addTransFlash('success', 'fulgurio.socialnetwork.remove_message.success_msg');
            }
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list'));
        }
        $templateName = 'FulgurioSocialNetworkBundle::confirm' . ($request->isXmlHttpRequest() ? 'Ajax' : '') . '.html.twig';
        return $this->render($templateName, array(
            'action' => $this->generateUrl(
                    'fulgurio_social_network_messenger_remove_message',
                    array('msgId' => $msgId)
            ),
            'title' => $this->translate('fulgurio.socialnetwork.remove_message.title'),
            'confirmationMessage' => $this->translate('fulgurio.socialnetwork.remove_message.confirm_msg'))
        );
    }

    /**
     * Get message and check if current user can see it
     *
     * @param integer $msgId
     * @param boolean $updateHasRead
     * @return Message
     * @throws NotFoundHttpException
     */
    protected function getMessage($msgId, $updateHasRead = FALSE)
    {
        if (FALSE == $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            throw new AccessDeniedHttpException();
        }
        $currentUser = $this->getUser();
        $relation = $this->getMessageTargetRepository()
                ->findOneBy(array(
                    'message' => $msgId,
                    'target' => $currentUser->getId())
        );
        if (!$relation)
        {
            throw new NotFoundHttpException();
        }
        if ($updateHasRead && $relation->getHasRead() == FALSE)
        {
            $relation->setHasRead(TRUE);
            $em = $this->getDoctrine()->getManager();
            $em->persist($relation);
            $em->flush();
        }
        return $this->getMessageRepository()->find($msgId);
    }

    /**
     * Get message repository
     *
     * @return MessageRepository
     */
    protected function getMessageRepository()
    {
        $className = $this->container->getParameter('fulgurio_social_network.messenger.message.class');
        return $this->getDoctrine()
                ->getRepository($className);
    }

    /**
     * Get message target repository
     *
     * @return MessageTargetRepository
     */
    protected function getMessageTargetRepository()
    {
        $className = $this->container->getParameter('fulgurio_social_network.messenger.message_target.class');
        return $this->getDoctrine()
                ->getRepository($className);
    }

    /**
     * Get user from given ID, and ckeck if he exists
     *
     * @throws NotFoundHttpException
     * @param number $userId
     * @return User
     */
    protected function getSpecifiedUser($userId)
    {
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        if (!$user = $this->getDoctrine()->getRepository($userClassName)->find($userId))
        {
            throw new NotFoundHttpException(
                $this->get('translator')->trans('fulgurio.socialnetwork.user_not_found', array(), 'admin_user')
            );
        }
        return $user;
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
        return $this->get('translator')->trans($message, $data, 'messenger');
    }
}