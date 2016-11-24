<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\SecurityContext;

class Messenger
{
    /**
     * Doctrine object
     *
     * @var Doctrine
     */
    protected $doctrine;

    /**
     * Security contect
     * @var SecurityContext
     */
    private $securityContext;

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
     * @param RegistryInterface $doctrine
     * @param SecurityContext $securityContext
     * @param string $messageClassName
     * @param string $messageTargetClassName
     */
    public function __construct(RegistryInterface $doctrine, SecurityContext $securityContext, $messageClassName, $messageTargetClassName)
    {
        $this->doctrine = $doctrine;
        $this->securityContext = $securityContext;
        $this->messageClassName = $messageClassName;
        $this->messageTargetClassName = $messageTargetClassName;
    }

    /**
     * Send message on messenger box
     *
     * @param User $userTgt
     * @param string $subject
     * @param string $content
     * @param boolean $canNotAnswer
     * @param string $typeOfMessage
     */
    public function sendMessage($userTgt, $subject, $content, $canNotAnswer = FALSE, $typeOfMessage = NULL)
    {
        $em = $this->doctrine->getEntityManager();
        $message = new $this->messageClassName();
        if ($this->securityContext &&
                $this->securityContext->getToken() &&
                $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $currentUser = $this->securityContext->getToken()->getUser();
            $message->setSender($currentUser);

            // Message from source
            $messageSource = new $this->messageTargetClassName();
            $messageSource->setTarget($currentUser);
            $messageSource->setMessage($message);
            $messageSource->setHasRead(TRUE);
            $message->addTarget($messageSource);
            $em->persist($messageSource);
        }
        $message->setSubject($subject);
        $message->setContent($content);
        $message->setAllowAnswer(!$canNotAnswer);
        if (!is_null($typeOfMessage))
        {
            $message->setTypeOfMessage($typeOfMessage);
        }
        // Message for target
        $messageTarget = new $this->messageTargetClassName();
        $messageTarget->setTarget($userTgt);
        $messageTarget->setMessage($message);
        $messageTarget->setHasRead(FALSE);
        $message->addTarget($messageTarget);
        $em->persist($messageTarget);

        $em->persist($message);
        $em->flush();
    }
}