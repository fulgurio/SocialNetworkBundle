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

        $user1 = $userManager->createUser();//new User();
        $user1->setUsername('user1');
        $user1->setPlainPassword('user1');
        $user1->setEmail('user1@example.com');
        $user1->setEnabled(TRUE);
        $userManager->updateUser($user1);

        $user2 = $userManager->createUser();
        $user2->setUsername('user2');
        $user2->setPlainPassword('user2');
        $user2->setEmail('user2@example.com');
        $user2->setEnabled(FALSE);
        $userManager->updateUser($user2);

        $user3 = $userManager->createUser();
        $user3->setUsername('user3');
        $user3->setPlainPassword('user3');
        $user3->setEmail('user3@example.com');
        $user3->setEnabled(TRUE);
        $user3->setAvatar('myAvatar.png');
        $userManager->updateUser($user3);

        $admin = $userManager->createUser();
        $admin->setUsername('admin');
        $admin->setPlainPassword('admin');
        $admin->setEmail('admin@example.com');
        $admin->setEnabled(TRUE);
        $admin->addRole('ROLE_ADMIN');
        $userManager->updateUser($admin);

        $superadmin = $userManager->createUser();
        $superadmin->setUsername('superadmin');
        $superadmin->setPlainPassword('superadmin');
        $superadmin->setEmail('superadmin@example.com');
        $superadmin->setEnabled(TRUE);
        $superadmin->addRole('ROLE_SUPER_ADMIN');
        $userManager->updateUser($superadmin);
    }
}