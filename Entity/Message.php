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
 * Fulgurio\SocialNetworkBundle\Entity\Message
 */
class Message
{
    /***************************************************************************
     *                             GENERATED CODE                              *
     **************************************************************************/

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $subject
     */
    private $subject;

    /**
     * @var text $content
     */
    private $content;

    /**
     * @var datetime $created_at
     */
    private $created_at;

    /**
     * @var datetime $updated_at
     */
    private $updated_at;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\MessageTarget
     */
    private $target;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\Message
     */
    private $children;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\Message
     */
    private $parent;

    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User
     */
    private $sender;

    public function __construct()
    {
        $this->target = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set subject
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text
     */
    public function getContent()
    {
        return $this->content;
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
     * Add target
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\MessageTarget $target
     */
    public function addMessageTarget(\Fulgurio\SocialNetworkBundle\Entity\MessageTarget $target)
    {
        $this->target[] = $target;
    }

    /**
     * Get target
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Add children
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\Message $children
     */
    public function addMessage(\Fulgurio\SocialNetworkBundle\Entity\Message $children)
    {
        $this->children[] = $children;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\Message $parent
     */
    public function setParent(\Fulgurio\SocialNetworkBundle\Entity\Message $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\Message
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set sender
     *
     * @param Fulgurio\SocialNetworkBundle\Entity\User $sender
     */
    public function setSender(\Fulgurio\SocialNetworkBundle\Entity\User $sender)
    {
        $this->sender = $sender;
    }

    /**
     * Get sender
     *
     * @return Fulgurio\SocialNetworkBundle\Entity\User
     */
    public function getSender()
    {
        return $this->sender;
    }
}