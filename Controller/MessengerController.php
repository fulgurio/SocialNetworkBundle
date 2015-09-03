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

use Fulgurio\SocialNetworkBundle\Form\Type\Messenger\AnswerMessageFormType;
use Fulgurio\SocialNetworkBundle\Form\Type\Messenger\NewMessageFormType;
use Fulgurio\SocialNetworkBundle\Form\Handler\Messenger\AnswerMessageFormHandler;
use Fulgurio\SocialNetworkBundle\Form\Handler\Messenger\NewMessageFormHandler;
use Fulgurio\SocialNetworkBundle\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

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
                array(
                    'messages' => $messages
                )
        );
    }

    /**
     * New message page
     *
     * @param number $userId
     * @return Response
     */
    public function newAction($userId = null)
    {
        $request = $this->get('request');
        $currentUser = $this->getUser();
        $messageClassName = $this->container->getParameter('fulgurio_social_network.messenger.message.class');
        $message = new $messageClassName();

        $selectedUsers = array();
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        if (!is_null($userId))
        {
            $selectedUsers = $this->getDoctrine()->getRepository($userClassName)->findBy(array('id' => $userId));
        }
        elseif ($selectedUsers = $request->get('users'))
        {
            $selectedUsers = $this->getDoctrine()->getRepository($userClassName)->findBy(array('id' => $selectedUsers));
        }
        $form = $this->createForm(
                new NewMessageFormType(
                        $currentUser,
                        $this->getDoctrine(),
                        $userClassName,
                        $this->container->getParameter('fulgurio_social_network.friendship.class'),
                        $this->container->getParameter('fulgurio_social_network.messenger.message_target.class')
                ), $message);
        $formHandler = new NewMessageFormHandler(
                $form,
                $this->getRequest(),
                $this->get('translator')
        );
        if ($formHandler->process(
                $this->getDoctrine(),
                $this->container->get('fulgurio_social_network.messenger_mailer'),
                $currentUser,
                $this->container->getParameter('fulgurio_social_network.messenger.message_target.class')))
        {
            $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.new_message.success_msg',
                            array(),
                            'messenger')
            );
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
        return $this->render('FulgurioSocialNetworkBundle:Messenger:new.html.twig', array(
            'form' => $form->createView(),
            'selectedUsers' => $selectedUsers,
        ));
    }

    /**
     * Messenger reply page
     *
     * @param number $msgId
     * @return Response
     */
    public function showAction($msgId)
    {
        $currentUser = $this->getUser();
        $message = $this->getMessage($msgId, TRUE);
        $data = array('message' => $message);
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        $userRepository = $this->getDoctrine()->getRepository($userClassName);
        $data['participants'] = $userRepository->findChatParticipants($message);
        if ($message->getAllowAnswer())
        {
            $messageClassName = $this->container->getParameter('fulgurio_social_network.messenger.message.class');
            $answer = new $messageClassName();
            $answer->setSubject('###RESPONSE###');
            $form = $this->createForm(new AnswerMessageFormType(), $answer);
            $formHandler = new AnswerMessageFormHandler(
                    $form,
                    $this->getRequest(),
                    $this->getDoctrine(),
                    $this->container->get('fulgurio_social_network.messenger_mailer'),
                    $this->container->getParameter('fulgurio_social_network.messenger.message.class'),
                    $this->container->getParameter('fulgurio_social_network.messenger.message_target.class')
            );
            if ($formHandler->process($message, $currentUser, $data['participants']))
            {
                $this->get('session')->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans(
                                'fulgurio.socialnetwork.answer_message.success_msg',
                                array(),
                                'messenger'));
                return $this->redirect(
                        $this->generateUrl(
                                'fulgurio_social_network_messenger_show_message',
                                array('msgId' => $msgId))
                );
            }
            $data['form'] = $form->createView();
        }
        $userFriendshipClassName = $this->container->getParameter('fulgurio_social_network.friendship.class');
        $tmpFriends = $this->getDoctrine()
                ->getRepository($userFriendshipClassName)
                ->findAcceptedFriends($currentUser);
        $data['friends'] = array();
        foreach ($tmpFriends as &$tmpFriend)
        {
            $data['friends'][$tmpFriend['id']] = $tmpFriend;
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Messenger:show.html.twig',
                $data
        );
    }

    /**
     * Messenger remove page
     *
     * @param number $msgId
     * @return Response
     */
    public function removeAction($msgId)
    {
        $request = $this->container->get('request');
        $currentUser = $this->getUser();
        $message = $this->getMessage($msgId);
        if ($request->request->get('confirm') === 'yes')
        {
            if (count($message->getTarget()) == 1)
            {
                // If we are the last (or only) user on message conversation,
                // we remove message user links, and the message with answer
                $em = $this->getDoctrine()->getManager();
                $em->remove($message);
                $em->flush();
            }
            else
            {
                // If there s some users who don't remove message, we just remove current user link with message
                $this->getMessageRepository()->removeUserMessageRelation($msgId, $currentUser);
            }
            $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.remove_message.success_msg',
                            array(),
                            'messenger')
                    );
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list'));
        }
        else if ($request->request->get('confirm') === 'no')
        {
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list'));
        }
        $templateName = 'FulgurioSocialNetworkBundle::confirm' . ($request->isXmlHttpRequest() ? 'Ajax' : '') . '.html.twig';
        return $this->render($templateName, array(
            'action' => $this->generateUrl(
                    'fulgurio_social_network_messenger_remove_message',
                    array('msgId' => $msgId)
            ),
            'confirmationMessage' => $this->get('translator')->trans(
                    'fulgurio.socialnetwork.remove_message.confirm_msg',
                    array(),
                    'messenger')
        ));
    }

    /**
     * Get message and check if current user can see it
     *
     * @param integer $msgId
     * @param boolean $updateHasRead
     * @return Message
     * @throws NotFoundHttpException
     */
    private function getMessage($msgId, $updateHasRead = FALSE)
    {
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

    protected function getMessageRepository()
    {
        $className = $this->container->getParameter('fulgurio_social_network.messenger.message.class');
        return $this->getDoctrine()
                ->getRepository($className);
    }

    protected function getMessageTargetRepository()
    {
        $className = $this->container->getParameter('fulgurio_social_network.messenger.message_target.class');
        return $this->getDoctrine()
                ->getRepository($className);
    }
}