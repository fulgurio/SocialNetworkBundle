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
use Fulgurio\SocialNetworkBundle\Entity\MessageTarget;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\DoctrineBundle\Registry;

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
     * Constructor
     *
     * @param Symfony\Component\Form\Form $form
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(Form $form, Request $request, Registry $doctrine)
    {
        $this->form = $form;
        $this->request = $request;
        $this->doctrine = $doctrine;
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
            $this->form->bindRequest($this->request);
            if ($this->form->isValid())
            {
                $answer = $this->form->getData();
                $answer->setParent($message);
                $answer->setSender($user);
                $em = $this->doctrine->getEntityManager();
                $em->persist($answer);
                foreach ($participants as $participant)
                {
                    $answerTarget = new MessageTarget();
                    $answerTarget->setHasRead(TRUE);
                    $answerTarget->setTarget($participant);
                    $answerTarget->setMessage($answer);
                    $em->persist($answerTarget);
                }
                $em->persist($message);
                $em->flush();
                $this->doctrine
                        ->getRepository('FulgurioSocialNetworkBundle:Message')
                        ->markRootAsUnread($message, $participants);
                return TRUE;
            }
        }
        return FALSE;
    }
}