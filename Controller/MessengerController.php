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

class MessengerController extends Controller
{
    /**
     * Messenger list page
     *
     * @return Response
     */
    public function listAction()
    {
        return $this->render(
                'FulgurioSocialNetworkBundle:Messenger:list.html.twig',
                array(
                    'messages' => $this->getMessagesList()
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
        $message = new Message();

        $selectedUsers = array();
        if (!is_null($userId))
        {
            $selectedUsers = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User')->findBy(array('id' => $userId));
        }
        elseif ($selectedUsers = $request->get('users'))
        {
            $selectedUsers = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User')->findBy(array('id' => $selectedUsers));
        }
        $form = $this->createForm(new NewMessageFormType($currentUser, $this->getDoctrine()), $message);
        $formHandler = new NewMessageFormHandler(
                $form,
                $this->getRequest(),
                $this->getDoctrine(),
                $this->container->get('fulgurio_social_network.messenger_mailer')
        );
        if ($formHandler->process($currentUser))
        {
            $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.new_message.success_msg',
                            array(),
                            'messenger')
            );
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list'));
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
        $messageRepository = $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:Message');
        $data['participants'] = $messageRepository->findParticipants($message);
        if ($message->getAllowAnswer())
        {
            $answer = new Message();
            $answer->setSubject('###RESPONSE###');
            $form = $this->createForm(new AnswerMessageFormType(), $answer);
            $formHandler = new AnswerMessageFormHandler(
                    $form,
                    $this->getRequest(),
                    $this->getDoctrine(),
                    $this->container->get('fulgurio_social_network.messenger_mailer')
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
        $tmpFriends = $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:UserFriendship')
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
            $messageRepository = $this->getDoctrine()
                    ->getRepository('FulgurioSocialNetworkBundle:Message');
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
                $messageRepository->removeUserMessageRelation($msgId, $currentUser);
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
     * Get messages root of current user
     */
    private function getMessagesList()
    {
        return $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:Message')
                ->findRootMessages($this->getUser());
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
        $relation = $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:MessageTarget')
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
        return $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:Message')
                ->find($msgId);
    }
}