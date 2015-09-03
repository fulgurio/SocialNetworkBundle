<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Form\Handler\MessengerList;

use Fulgurio\SocialNetworkBundle\Form\Handler\AbstractAjaxForm;
use Doctrine\Bundle\DoctrineBundle\Registry;

class NewListFormHandler extends AbstractAjaxForm
{
    /**
     * Processing form values
     *
     * @param Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @param array $users
     * @param string $userClassName
     * @return boolean
     */
    public function process(Registry $doctrine, $users, $userClassName)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
//                $message = $this->form->getData();
//                $message->setSender($user);
//                $targets = $message->getTarget();
//                foreach ($targets as $target)
//                {
//                    $mailer->sendMessageEmailMessage($target->getTarget(), $message);
//                }
//                $messageTarget = new $this->messageTargetClassName();
//                $messageTarget->setTarget($user);
//                $messageTarget->setMessage($message);
//                $messageTarget->setHasRead(TRUE);
//                $message->addTarget($messageTarget);
//                $em->persist($messageTarget);
//                $em->persist($message);
//                $em->flush();
                $userRepo = $doctrine->getRepository($userClassName);
                $group = $this->form->getData();
                if (!$group->getId())
                {
                    foreach ($users as $user)
                    {
                        $removeUser = TRUE;
//                        foreach ($this->form->getData()->idUsers as $userId)
//                        {
//                            if ($user->getId() == $userId)
//                            {
//                                $removeUser = FALSE;
//                            }
//                        }
//                        if ($removeUser)
//                        {
//                            $users->removeElement($user);
//                        }
                    }
                }
//                foreach ($this->form->getData()->idUsers as $userId)
//                {
//                    $hadUser = TRUE;
//                    if (!is_null($groupId))
//                    {
//                        foreach ($users as $user)
//                        {
//                            if ($user->getId() == $userId)
//                            {
//                                $hadUser = FALSE;
//                                break;
//                            }
//                        }
//                    }
//                    if ($hadUser)
//                    {
//                        $group->addUser($userRepo->findOneById($userId));
//                    }
//                }
                $em = $doctrine->getManager();
                $em->persist($group);
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
}