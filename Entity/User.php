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
use Doctrine\ORM\Mapping as ORM;

/**
 * User entity
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class User extends BaseUser
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $avatarFile
     */
    private $avatarFile;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->friends = new \Doctrine\Common\Collections\ArrayCollection();
        $this->msgSender = new \Doctrine\Common\Collections\ArrayCollection();
        $this->msgTarget = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set avatarFile
     *
     * @param string $avatarFile
     */
    public function setAvatarFile($avatarFile)
    {
        $this->avatarFile = $avatarFile;

        // We simulate a change on submited form, to save data in database
        $this->avatar .= '#CHANGE#';
    }

    /**
     * Get avatarFile
     *
     * @return string
     */
    public function getAvatarFile()
    {
        return $this->avatarFile;
    }

    /**
     * Display avatar
     *
     * @return string
     */
    public function displayAvatar()
    {
        return '/' . $this->getUploadDir() . $this->avatar;
    }

    /**
     * Upload directory
     */
    public function getUploadDir()
    {
        return 'uploads/' . $this->getId() . '/';
    }

    /**
     * Get absolut upload directory
     */
    public function getUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * Get a randomw filename, and check if file does'nt exist
     *
     * @param UploadedFile $file
     * @param string $path
     */
    public function getUniqName($file, $path)
    {
        $filename = uniqid() . '.' . $file->guessExtension();
        if (!file_exists($path . $filename))
        {
            return ($filename);
        }
        $this->getUniqName($file, $path);
    }

    /**
     * @ORM\PrePersist
     */
    public function preUpload()
    {
        if (null !== $this->avatarFile)
        {
            $this->removeUpload();
            $this->avatar = $this->getUniqName($this->avatarFile, $this->getUploadRootDir());
        }
    }

    /**
     * @ORM\PostPersist
     */
    public function upload()
    {
        if (null !== $this->avatarFile)
        {
            $this->avatarFile->move($this->getUploadRootDir(), $this->avatar);
            $this->image_shrink($this->getUploadRootDir(). $this->avatar, $this->getUploadRootDir(). $this->avatar, 50, 50, 80);
            unset($this->avatarFile);
        }
    }

    /**
     * @ORM\PostRemove
     */
    public function removeUpload()
    {
        if ($this->avatar != '#CHANGE#')
        {
            @unlink($this->getUploadRootDir() . substr($this->avatar, 0, -strlen('#CHANGE#')));
        }
    }

    /**
     * Shrink picture
     *
     * @param type $sourcefile
     * @param type $destfile
     * @param type $fw
     * @param type $fh
     * @param type $jpegquality
     * @return string
     */
    private function image_shrink($sourcefile, $destfile, $fw, $fh, $jpegquality = 100)
    {
        list($ow, $oh, $from_type) = getimagesize($sourcefile);
        switch($from_type)
        {
            case 1: // GIF
                $srcImage = imageCreateFromGif($sourcefile);
                break;
            case 2: // JPG
                $srcImage = imageCreateFromJpeg($sourcefile);
                break;
            case 3: // PNG
                $srcImage = imageCreateFromPng($sourcefile);
                break;
            default:
                return;
        }
        if (($fw / $ow) > ($fh / $oh))
        {
            $tempw = $fw;
            $temph = ($fw / $ow) * $oh;
        }
        else
        {
            $tempw = ($fh / $oh) * $ow;
            $temph = $fh;
        }
        $tempImage = imageCreateTrueColor($fw, $fh);
        imagecopyresampled($tempImage, $srcImage, ($fw - $tempw) / 2, ($fh - $temph) / 2, 0, 0, $tempw, $temph, $ow, $oh);
        imageJpeg($tempImage, $destfile, $jpegquality);
        return getimagesize($destfile);
    }

    /**
     * Get avatar url from an array of data
     *
     * @param array $user
     * @return string
     */
    static function getAvatarUrl(array $user)
    {
        return '/uploads/' . $user['id'] . '/' . $user['avatar'];
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
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $friends;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $msgSender;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $msgTarget;


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

    /**
     * Add friends
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\UserFriendship $friends
     * @return User
     */
    public function addFriend(\Fulgurio\SocialNetworkBundle\Entity\UserFriendship $friends)
    {
        $this->friends[] = $friends;

        return $this;
    }

    /**
     * Remove friends
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\UserFriendship $friends
     */
    public function removeFriend(\Fulgurio\SocialNetworkBundle\Entity\UserFriendship $friends)
    {
        $this->friends->removeElement($friends);
    }

    /**
     * Get friends
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * Add msgSender
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\Message $msgSender
     * @return User
     */
    public function addMsgSender(\Fulgurio\SocialNetworkBundle\Entity\Message $msgSender)
    {
        $this->msgSender[] = $msgSender;

        return $this;
    }

    /**
     * Remove msgSender
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\Message $msgSender
     */
    public function removeMsgSender(\Fulgurio\SocialNetworkBundle\Entity\Message $msgSender)
    {
        $this->msgSender->removeElement($msgSender);
    }

    /**
     * Get msgSender
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMsgSender()
    {
        return $this->msgSender;
    }

    /**
     * Add msgTarget
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\MessageTarget $msgTarget
     * @return User
     */
    public function addMsgTarget(\Fulgurio\SocialNetworkBundle\Entity\MessageTarget $msgTarget)
    {
        $this->msgTarget[] = $msgTarget;

        return $this;
    }

    /**
     * Remove msgTarget
     *
     * @param \Fulgurio\SocialNetworkBundle\Entity\MessageTarget $msgTarget
     */
    public function removeMsgTarget(\Fulgurio\SocialNetworkBundle\Entity\MessageTarget $msgTarget)
    {
        $this->msgTarget->removeElement($msgTarget);
    }

    /**
     * Get msgTarget
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMsgTarget()
    {
        return $this->msgTarget;
    }
}
