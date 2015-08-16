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
abstract class UserFriendship
{
    const ASKING_STATUS = 'asking';
    const ACCEPTED_STATUS = 'accepted';
    const PENDING_STATUS = 'pending';
    const REFUSED_STATUS = 'refused';
    const REMOVED_STATUS = 'removed';

    /**
     * Available status
     *
     * @var array
     */
    private $availableStatus = array(self::ASKING_STATUS, self::ACCEPTED_STATUS, self::PENDING_STATUS, self::REFUSED_STATUS, self::REMOVED_STATUS);

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
            $this->status = self::PENDING_STATUS;
        }
    }

    /***************************************************************************
     *                             GENERATED CODE                              *
     **************************************************************************/

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;


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
     * @param integer $nbRefusals
     * @return UserFriendship
     */
    public function setNbRefusals($nbRefusals)
    {
        $this->nb_refusals = $nbRefusals;

        return $this;
    }

    /**
     * Get nb_refusals
     *
     * @return integer
     */
    public function getNbRefusals()
    {
        return $this->nb_refusals;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return UserFriendship
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return UserFriendship
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
