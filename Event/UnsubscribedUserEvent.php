<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Fulgurio\SocialNetworkBundle\Entity\User;

class UnsubscribedUserEvent extends Event
{
    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User
     */
    private $user;


    /**
     * Constructor
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * $user getter
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}