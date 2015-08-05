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

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fulgurio\SocialNetworkBundle\Entity\Message
 */
class Message
{
    /**
     * @var boolean $allowAnswer
     */
    private $allowAnswer = TRUE;

    /**
     * @var File $file
     */
    public $file;


    /**
     * Set file
     *
     * @param File $file
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        if ($file)
        {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated_at = new \DateTime('now');
        }
    }

    /**
     * Get file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
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
    private $subject;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $typeOfMessage;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $target;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Fulgurio\SocialNetworkBundle\Entity\Message
     */
    private $parent;

    /**
     * @var \Fulgurio\SocialNetworkBundle\Entity\User
     */
    private $sender;

    /**
     * Constructor
     */
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
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
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
     * @param string $content
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set allowAnswer
     *
     * @param boolean $allowAnswer
     * @return Message
     */
    public function setAllowAnswer($allowAnswer)
    {
        $this->allowAnswer = $allowAnswer;

        return $this;
    }

    /**
     * Get allowAnswer
     *
     * @return boolean
     */
    public function getAllowAnswer()
    {
        return $this->allowAnswer;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Message
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set typeOfMessage
     *
     * @param string $typeOfMessage
     * @return Message
     */
    public function setTypeOfMessage($typeOfMessage)
    {
        $this->typeOfMessage = $typeOfMessage;

        return $this;
    }

    /**
     * Get typeOfMessage
     *
     * @return string
     */
    public function getTypeOfMessage()
    {
        return $this->typeOfMessage;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Message
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
     * @return Message
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

    /**
     * Add target
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\MessageTarget $target
     * @return Message
     */
    public function addTarget(\Fulgurio\SocialNetworkBundle\Entity\MessageTarget $target)
    {
        $this->target[] = $target;

        return $this;
    }

    /**
     * Remove target
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\MessageTarget $target
     */
    public function removeTarget(\Fulgurio\SocialNetworkBundle\Entity\MessageTarget $target)
    {
        $this->target->removeElement($target);
    }

    /**
     * Get target
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Add children
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\Message $children
     * @return Message
     */
    public function addChild(\Fulgurio\SocialNetworkBundle\Entity\Message $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\Message $children
     */
    public function removeChild(\Fulgurio\SocialNetworkBundle\Entity\Message $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\Message $parent
     * @return Message
     */
    public function setParent(\Fulgurio\SocialNetworkBundle\Entity\Message $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Fulgurio\SocialNetworkBundle\Entity\Message
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set sender
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\User $sender
     * @return Message
     */
    public function setSender(\Fulgurio\SocialNetworkBundle\Entity\User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \Fulgurio\SocialNetworkBundle\Entity\User
     */
    public function getSender()
    {
        return $this->sender;
    }
}
