<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Form\Handler\Messenger;

use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Entity\Message;
use Fulgurio\SocialNetworkBundle\Mailer\MessengerMailer;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Registry;

class AnswerMessageFormHandler
{
    /**
     * @var Symfony\Component\Form\Form
     */
    private $form;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var Symfony\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    /**
     * @var Fulgurio\SocialNetworkBundle\Mailer\MessengerMailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $messageClassName;

    /**
     * @var string
     */
    private $messageTargetClassName;


    /**
     * Constructor
     *
     * @param Symfony\Component\Form\Form $form
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Bundle\DoctrineBundle\Registry $doctrine
     * @param $mailer
     * @param string $messageClassName
     * @param string $messageTargetClassName
     */
    public function __construct(Form $form, Request $request, Registry $doctrine, MessengerMailer $mailer, $messageClassName, $messageTargetClassName)
    {
        $this->form = $form;
        $this->request = $request;
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->messageClassName = $messageClassName;
        $this->messageTargetClassName = $messageTargetClassName;
    }

    /**
     * Processing form values
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\Message $message
     * @param Fulgurio\SocialNetworkBundle\Entity\User $user
     * @param $participants
     * @return boolean
     */
    public function process(Message $message, User $user, $participants)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
                $answer = $this->form->getData();
                $answer->setParent($message);
                $answer->setSender($user);
                $em = $this->doctrine->getManager();
                $em->persist($answer);
                $unreadMessageUsers = array();
                foreach ($participants as $participant)
                {
                    $answerTarget = new $this->messageTargetClassName();
                    $answerTarget->setHasRead(TRUE);
                    $answerTarget->setTarget($participant);
                    $answerTarget->setMessage($answer);
                    $em->persist($answerTarget);
                    // We do not set unread message for current user
                    if ($participant->getId() !== $user->getId())
                    {
                        $this->mailer->sendAnswerEmailMessage(
                                $participant, $message, $answer);
                        $unreadMessageUsers[] = $participant;
                    }
                }
                $em->persist($message);
                $em->flush();
                $this->doctrine
                        ->getRepository($this->messageClassName)
                        ->markRootAsUnread($message, $unreadMessageUsers);
                return TRUE;
            }
        }
        return FALSE;
    }
}