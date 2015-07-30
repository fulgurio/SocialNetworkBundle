<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Fulgurio\SocialNetworkBundle\Entity\UserFriendship
 */
class UserFriendship
{
    /**
     * Available status
     *
     * @var array
     */
    private $availableStatus = array('asking', 'accepted', 'pending', 'refused', 'removed');

    /**
     * @var smallint $nb_refusals
     */
    private $nb_refusals = 0;


    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        if (in_array($status, $this->availableStatus))
        {
            $this->status = $status;
        }
        else
        {
            $this->status = 'pending';
        }
    }

    /***************************************************************************
     *                             GENERATED CODE                              *
     **************************************************************************/

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var datetime $created_at
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     */
    private $updated_at;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User
     */
    private $user_src;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User
     */
    private $user_tgt;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set nb_refusals
     *
     * @param smallint $nbRefusals
     */
    public function setNbRefusals($nbRefusals)
    {
        $this->nb_refusals = $nbRefusals;
    }

    /**
     * Get nb_refusals
     *
     * @return smallint
     */
    public function getNbRefusals()
    {
        return $this->nb_refusals;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set user_src
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\User $userSrc
     */
    public function setUserSrc(\Fulgurio\SocialNetworkBundle\Entity\User $userSrc)
    {
        $this->user_src = $userSrc;
    }

    /**
     * Get user_src
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\User
     */
    public function getUserSrc()
    {
        return $this->user_src;
    }

    /**
     * Set user_tgt
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\User $userTgt
     */
    public function setUserTgt(\Fulgurio\SocialNetworkBundle\Entity\User $userTgt)
    {
        $this->user_tgt = $userTgt;
    }

    /**
     * Get user_tgt
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\User
     */
    public function getUserTgt()
    {
        return $this->user_tgt;
    }
}