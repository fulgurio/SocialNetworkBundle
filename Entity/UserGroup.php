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
 * Fulgurio\SocialNetworkBundle\Entity\UserGroup
 */
abstract class UserGroup
{
    
    const MESSENGER_LIST_TYPE = 'messengerList';

    /**
     * @var integer
     */
    private $nb_of_relationship = 0;

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
    private $name;

    /**
     * @var string
     */
    private $type_of_group;

    /**
     * @var string
     */
    private $type_of_owner;

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
     * Set name
     *
     * @param string $name
     * @return UserGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type_of_group
     *
     * @param string $typeOfGroup
     * @return UserGroup
     */
    public function setTypeOfGroup($typeOfGroup)
    {
        $this->type_of_group = $typeOfGroup;

        return $this;
    }

    /**
     * Get type_of_group
     *
     * @return string
     */
    public function getTypeOfGroup()
    {
        return $this->type_of_group;
    }

    /**
     * Set type_of_owner
     *
     * @param string $typeOfOwner
     * @return UserGroup
     */
    public function setTypeOfOwner($typeOfOwner)
    {
        $this->type_of_owner = $typeOfOwner;

        return $this;
    }

    /**
     * Get type_of_owner
     *
     * @return string
     */
    public function getTypeOfOwner()
    {
        return $this->type_of_owner;
    }

    /**
     * Set nb_of_relationship
     *
     * @param integer $nbOfRelationship
     * @return UserGroup
     */
    public function setNbOfRelationship($nbOfRelationship)
    {
        $this->nb_of_relationship = $nbOfRelationship;

        return $this;
    }

    /**
     * Get nb_of_relationship
     *
     * @return integer
     */
    public function getNbOfRelationship()
    {
        return $this->nb_of_relationship;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return UserGroup
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
     * @return UserGroup
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
