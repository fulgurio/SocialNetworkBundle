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
    protected $doctrine;


    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions()
    {
        return array(
            'nbOfPendingUser' => new \Twig_Function_Method($this, 'nbOfPendingUser', array('is_safe' => array('html'))),
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
                ->getRepository('FulgurioSocialNetworkBundle:UserFriendship')
                ->countPendingUserOfFrienship($user);
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