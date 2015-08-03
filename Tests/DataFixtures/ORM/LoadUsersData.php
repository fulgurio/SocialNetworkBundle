<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Tests\DataFixtures\ORM;

use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Entity\UserFriendship;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Users data fixtures for tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class LoadUsersData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        $userDisabled = $this->createUser('userDisabled', NULL, FALSE);

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');
        $user2->setAvatar('myAvatar.png');
        $userManager->updateUser($user2);

        $user3 = $this->createUser('user3');
        $user4 = $this->createUser('user4');
        $user5 = $this->createUser('user5');

        $this->createFriendship($user1, $user3, UserFriendship::ACCEPTED_STATUS);
        $this->createFriendship($user4, $user1, UserFriendship::ASKING_STATUS);

        $admin = $this->createUser('admin', array('ROLE_ADMIN'));

        $superadmin = $this->createUser('superadmin', array('ROLE_SUPER_ADMIN'));
    }

    /**
     * Create a user
     *
     * @param string $username
     * @param array $roles
     * @param boolean $enabled
     * @return User
     */
    private function createUser($username, array $roles = NULL, $enabled = TRUE)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setPlainPassword($username);
        $user->setEmail($username . '@example.com');
        $user->setEnabled($enabled);
        if ($roles)
        {
            foreach ($roles as $role)
            {
                $user->addRole($role);
            }
        }
        $userManager->updateUser($user);
        return $user;
    }

    /**
     * Create a friendship between two users
     *
     * @param User $userSource
     * @param User  $userTarget
     * @param string $status
     */
    private function createFriendship(User $userSource, User $userTarget, $status)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        if ($status == UserFriendship::ACCEPTED_STATUS)
        {
            $status1 = UserFriendship::ACCEPTED_STATUS;
            $status2 = UserFriendship::ACCEPTED_STATUS;
        }
        elseif ($status == UserFriendship::ASKING_STATUS)
        {
            $status1 = UserFriendship::PENDING_STATUS;
            $status2 = UserFriendship::ASKING_STATUS;
        }
        $friendship1 = new UserFriendship();
        $friendship1->setUserSrc($userSource);
        $friendship1->setUserTgt($userTarget);
        $friendship1->setStatus($status1);

        $friendship2 = new UserFriendship();
        $friendship2->setUserSrc($userTarget);
        $friendship2->setUserTgt($userSource);
        $friendship2->setStatus($status2);
        $em->persist($friendship1);
        $em->persist($friendship2);
        $em->flush();
    }
}