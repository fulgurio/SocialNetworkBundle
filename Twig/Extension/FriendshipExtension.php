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

class FriendshipExtension extends \Twig_Extension
{
    /**
     * @var Symfony\Bridge\Doctrine\RegistryInterface
     */
    private $doctrine;

    /**
     * @var string
     */
    private $userFriendshipClassName;


    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string $userFriendshipClassName
     */
    public function __construct(RegistryInterface $doctrine, $userFriendshipClassName)
    {
        $this->doctrine = $doctrine;
        $this->userFriendshipClassName = $userFriendshipClassName;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('nbOfPendingUser',         array($this, 'nbOfPendingUser'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('areFriends',              array($this, 'areFriends'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('havePendingInvitation',   array($this, 'havePendingInvitation'), array('is_safe' => array('html')))
        );
    }

    /**
     * Display pending invitation
     *
     * @param User $user
     * @return number
     */
    public function nbOfPendingUser(User $user)
    {
        return $this->doctrine
                ->getRepository($this->userFriendshipClassName)
                ->countPendingUserOfFrienship($user);
    }

    /**
     * Check if 2 users are friends
     *
     * @param User $user1
     * @param User $user2
     * @return boolean
     */
    public function areFriends(User $user1, User $user2)
    {
        return $this->doctrine
                ->getRepository($this->userFriendshipClassName)
                ->areFriends($user1, $user2) ? TRUE : FALSE;
    }

    /**
     * Check if 2 users have pending invitations relationship
     *
     * @param User $user1
     * @param User $user2
     * @return boolean
     */
    public function havePendingInvitation(User $user1, User $user2)
    {
        return $this->doctrine
                ->getRepository($this->userFriendshipClassName)
                ->havePendingInvitation($user1, $user2) ? TRUE : FALSE;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'friendship';
    }
}