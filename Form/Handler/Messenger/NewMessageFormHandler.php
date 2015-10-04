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

use Fulgurio\SocialNetworkBundle\Form\Handler\AbstractAjaxForm;
use Fulgurio\SocialNetworkBundle\Entity\Message;
use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Entity\UserGroup;
use Fulgurio\SocialNetworkBundle\Mailer\MessengerMailer;
use Doctrine\Bundle\DoctrineBundle\Registry;

class NewMessageFormHandler extends AbstractAjaxForm
{
    /**
     * @var string
     */
    private $messageTargetClassName;

    /**
     * @var Registry
     */
    private $doctrine;


    /**
     * Processing form values
     *
     * @param Registry $doctrine
     * @param MessengerMailer $mailer
     * @param User $user
     * @param string $messageTargetClassName
     * @return boolean
     */
    public function process(Registry $doctrine, MessengerMailer $mailer, User $user)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
                $this->doctrine = $doctrine;
                $message = $this->form->getData();
                $message->setSender($user);
                $message->setContent($this->applyFilter($message->getContent()));
                if ($this->form->has('group') && $this->form->get('group')->getData() != NULL)
                {
                    $this->addTargetFromGroup($this->form->get('group')->getData(), $message);
                }
                $targets = $message->getTarget();
                foreach ($targets as $target)
                {
                    $mailer->sendMessageEmailMessage($target->getTarget(), $message);
                }
                $messageTarget = new $this->messageTargetClassName();
                $messageTarget->setTarget($user);
                $messageTarget->setMessage($message);
                $messageTarget->setHasRead(TRUE);
                $message->addTarget($messageTarget);
                $em = $doctrine->getManager();
                $em->persist($messageTarget);
                $em->persist($message);
                $em->flush();
                return TRUE;
            }
            else
            {
                $this->hasErrors = TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Add target from selected group list
     *
     * @param UserGroup $group
     * @param Message $message
     */
    protected function addTargetFromGroup(UserGroup $group, Message $message)
    {
        $usersId = array();
        $existingTargets = $message->getTarget();
        foreach ($existingTargets as $existingTarget)
        {
            $usersId[$existingTarget->getId()] = $existingTarget->getId();
        }
        $users = $group->getUsers();
        foreach ($users as $user)
        {
            if (isset($usersId[$user->getId()]))
            {
                continue;
            }
            $messageTarget = new $this->messageTargetClassName();
            $messageTarget->setTarget($user);
            $messageTarget->setMessage($message);
            $messageTarget->setHasRead(FALSE);
            $message->addTarget($messageTarget);
            $this->doctrine->getManager()->persist($messageTarget);
        }
    }

    /**
     * Apply content filter (remove tags and add br)
     *
     * @param string $content
     * @return string
     */
    protected function applyFilter($content)
    {
        return nl2br(htmlentities($content));
    }

    /**
     * $messageTargetClassName setter
     * @param string $messageTargetClassName
     * @return \Fulgurio\SocialNetworkBundle\Form\Handler\Messenger\NewMessageFormHandler
     */
    public function setMessageTargetClassName($messageTargetClassName)
    {
        $this->messageTargetClassName = $messageTargetClassName;

        return $this;
    }
}