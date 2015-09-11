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
use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Mailer\MessengerMailer;
use Doctrine\Bundle\DoctrineBundle\Registry;

class NewMessageFormHandler extends AbstractAjaxForm
{
    /**
     * @var string
     */
    private $messageTargetClassName;


    /**
     * Processing form values
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\User $user
     * @return boolean
     */
    public function process(Registry $doctrine, MessengerMailer $mailer, User $user, $messageTargetClassName)
    {
        $this->messageTargetClassName = $messageTargetClassName;
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
                $message = $this->form->getData();
                $message->setSender($user);
                $message->setContent($this->applyFilter($message->getContent()));
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
     * Apply content filter (remove tags and add br)
     *
     * @param string $content
     * @return string
     */
    protected function applyFilter($content)
    {
        return nl2br(strip_tags($content));
    }
}