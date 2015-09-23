<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Twig\Extension;

use Fulgurio\SocialNetworkBundle\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MessengerExtension extends \Twig_Extension
{
    /**
     * @var Symfony\Bridge\Doctrine\RegistryInterface
     */
    private $doctrine;

    /**
     * @var string
     */
    private $messageClassName;


    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine, $messageClassName)
    {
        $this->doctrine = $doctrine;
        $this->messageClassName = $messageClassName;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('nbOfUnreadMessage', array($this, 'nbOfUnreadMessage'), array('is_safe' => array('html')))
        );
    }

    /**
     * Display number or unread message
     *
     * @param User $user
     * @return number
     */
    public function nbOfUnreadMessage(User $user)
    {
        return $this->doctrine
                ->getRepository($this->messageClassName)
                ->countUnreadMessage($user);
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'messenger';
    }
}