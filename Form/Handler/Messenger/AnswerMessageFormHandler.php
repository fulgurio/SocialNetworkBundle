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
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class AnswerMessageFormHandler
{
    /**
     * @var Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

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
     * @param Form $form
     * @param Request $request
     * @param TranslatorInterface $translator
     */
    public function __construct(Form $form, Request $request, TranslatorInterface $translator)
    {
        $this->form = $form;
        $this->request = $request;
        $this->translator = $translator;
    }

    /**
     * Processing form values
     *
     * @param Registry $doctrine
     * @param MessengerMailer $mailer
     * @param User $user
     * @param Fulgurio\SocialNetworkBundle\Entity\Message $message
     * @param $participants
     * @return boolean
     */
    public function process(Registry $doctrine, MessengerMailer $mailer, User $user, Message $message, $participants)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
                $answer = $this->form->getData();
                $answer->setParent($message)
                        ->setSubject('###RESPONSE###')
                        ->setSender($user)
                        ->setContent($this->applyFilter($answer->getContent()));
                $em = $doctrine->getManager();
                $em->persist($answer);
                $unreadMessageUsers = array();
                foreach ($participants as $participant)
                {
                    $answerTarget = new $this->messageTargetClassName();
                    $answerTarget->setHasRead(TRUE)
                            ->setTarget($participant)
                            ->setMessage($answer);
                    $em->persist($answerTarget);
                    // We do not set unread message for current user
                    if ($participant->getId() !== $user->getId())
                    {
                        $mailer->sendAnswerEmailMessage(
                                $participant, $message, $answer);
                        $unreadMessageUsers[] = $participant;
                    }
                }
                $em->persist($message);
                $em->flush();
                $doctrine
                        ->getRepository($this->messageClassName)
                        ->markRootAsUnreadAndUndeleted($message, $unreadMessageUsers);
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Apply content filter (remove tags and add br)
     *
     * @param string $content
     * @return string
     */
    protected function applyFilter($content)
    {
        if (ini_get('default_charset'))
        {
            return nl2br(htmlentities($content));
        }
        else
        {
            return nl2br(htmlentities($content, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
        }
    }

    /**
     * $messageClassName setter
     * @param string $messageClassName
     * @return \Fulgurio\SocialNetworkBundle\Form\Handler\Messenger\AnswerMessageFormHandler
     */
    public function setMessageClassName($messageClassName)
    {
        $this->messageClassName = $messageClassName;

        return $this;
    }

    /**
     * $messageTargetClassName setter
     * @param string $messageTargetClassName
     * @return \Fulgurio\SocialNetworkBundle\Form\Handler\Messenger\AnswerMessageFormHandler
     */
    public function setMessageTargetClassName($messageTargetClassName)
    {
        $this->messageTargetClassName = $messageTargetClassName;

        return $this;
    }

    /**
     * Translate message
     *
     * @param string $message
     * @return string
     */
    protected function translate($message)
    {
        return $this->translator->trans($message, array(), 'messenger');
    }
}