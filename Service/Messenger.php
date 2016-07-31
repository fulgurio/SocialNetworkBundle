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
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class Messenger
{
    /**
     * @var Doctrine
     */
    protected $doctrine;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

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
     * @param AuthorizationChecker $authorizationChecker
     * @param SecurityContext $tokenStorage
     * @param string $messageClassName
     * @param string $messageTargetClassName
     */
    public function __construct(RegistryInterface $doctrine, AuthorizationChecker $authorizationChecker, TokenStorage $tokenStorage, $messageClassName, $messageTargetClassName)
    {
        $this->doctrine = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
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
        $message = new $this->messageClassName();
        if ($this->tokenStorage &&
                $this->tokenStorage->getToken() &&
                $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $message->setSender($this->tokenStorage->getToken()->getUser());
        }
        $message->setSubject($subject);
        $message->setContent($content);
        $message->setAllowAnswer(!$canNotAnswer);
        if (!is_null($typeOfMessage))
        {
            $message->setTypeOfMessage($typeOfMessage);
        }
        $messageTarget = new $this->messageTargetClassName();
        $messageTarget->setTarget($userTgt);
        $messageTarget->setMessage($message);
        $messageTarget->setHasRead(FALSE);
        $message->addTarget($messageTarget);
        $em = $this->doctrine->getEntityManager();
        $em->persist($messageTarget);
        $em->persist($message);
        $em->flush();
    }
}