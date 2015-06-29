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
 * Fulgurio\SocialNetworkBundle\Entity\MessageTarget
 */
class MessageTarget
{
    /**
     * @var boolean $has_read
     */
    private $has_read = 0;

    /***************************************************************************
     *                             GENERATED CODE                              *
     **************************************************************************/
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var datetime $created_at
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     */
    private $updated_at;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\Message
     */
    private $message;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User
     */
    private $target;


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
     * Set has_read
     *
     * @param boolean $hasRead
     */
    public function setHasRead($hasRead)
    {
        $this->has_read = $hasRead;
    }

    /**
     * Get has_read
     *
     * @return boolean
     */
    public function getHasRead()
    {
        return $this->has_read;
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
     * Set message
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\Message $message
     */
    public function setMessage(\Fulgurio\SocialNetworkBundle\Entity\Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set target
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\User $target
     */
    public function setTarget(\Fulgurio\SocialNetworkBundle\Entity\User $target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\User
     */
    public function getTarget()
    {
        return $this->target;
    }
}