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
     * @var datetime $created_at
     */
    private $created_at;

    /**
     * @var datetime $updated_at
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
}