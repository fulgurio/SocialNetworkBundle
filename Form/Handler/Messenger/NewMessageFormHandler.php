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
use Fulgurio\SocialNetworkBundle\Entity\MessageTarget;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Registry;

class NewMessageFormHandler
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
     * @param Fulgurio\SocialNetworkBundle\Entity\User $user
     * @return boolean
     */
    public function process(User $user)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
                $message = $this->form->getData();
                $message->setSender($user);
                $messageTarget = new MessageTarget();
                $messageTarget->setTarget($user);
                $messageTarget->setMessage($message);
                $messageTarget->setHasRead(TRUE);
                $message->addMessageTarget($messageTarget);
                $em = $this->doctrine->getManager();
                $em->persist($messageTarget);
                $em->persist($message);
                $em->flush();
                return TRUE;
            }
        }
        return FALSE;
    }
}