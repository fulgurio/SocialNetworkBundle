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
use Fulgurio\SocialNetworkBundle\Entity\Message;
use Fulgurio\SocialNetworkBundle\Entity\MessageTarget;

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
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param SecurityContext $securityContext
     */
    public function __construct(RegistryInterface $doctrine, SecurityContext $securityContext)
    {
        $this->doctrine = $doctrine;
        $this->securityContext = $securityContext;
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
        $message = new Message();
        $message->setSender($this->securityContext->getToken()->getUser());
        $message->setSubject($subject);
        $message->setContent($content);
        $message->setAllowAnswer(!$canNotAnswer);
        if (!is_null($typeOfMessage))
        {
            $message->setTypeOfMessage($typeOfMessage);
        }
        $messageTarget = new MessageTarget();
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