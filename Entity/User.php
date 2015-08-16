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

use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;

/**
 * User entity
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
abstract class User extends BaseUser
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var File $avatarFile
     */
    protected $avatarFile;

    /**
     * @var boolean $send_msg_to_email
     */
    private $send_msg_to_email = TRUE;


    /**
     * Set avatar_file
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setAvatarFile(File $image = null)
    {
        $this->avatarFile = $image;

        if ($image)
        {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated_at = new \DateTime('now');
        }
    }

    /**
     * Get avatar_file
     *
     * @return File
     */
    public function getAvatarFile()
    {
        return $this->avatarFile;
    }

    /***************************************************************************
     *                             GENERATED CODE                              *
     **************************************************************************/

    /**
     * @var string
     */
    private $avatar;

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
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set send_msg_to_email
     *
     * @param boolean $sendMsgToEmail
     * @return User
     */
    public function setSendMsgToEmail($sendMsgToEmail)
    {
        $this->send_msg_to_email = $sendMsgToEmail;

        return $this;
    }

    /**
     * Get send_msg_to_email
     *
     * @return boolean
     */
    public function getSendMsgToEmail()
    {
        return $this->send_msg_to_email;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return User
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
     * @return User
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
