<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Tests\Controller;

use Fulgurio\SocialNetworkBundle\Tests\Controller\WebTestCase;

/**
 * Front page controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class FriendshipControllerTest extends WebTestCase
{
    /**
     * No friend test
     */
    public function testNoFriendAction()
    {
        $client = $this->getUserLoggedClient('user2', 'user2');
        $crawler = $client->request('GET', '/friends/');
        $this->assertCount(1, $crawler->filter('body:contains("fulgurio.socialnetwork.no_friend")'));
    }

    /**
     * Has friend test
     */
    public function testHasFriendsAction()
    {
        $client = $this->getUserLoggedClient('user1', 'user1');
        $crawler = $client->request('GET', '/friends/');
        $this->assertCount(0, $crawler->filter('body:contains("fulgurio.socialnetwork.no_friend")'));
    }

    /**
     * Add disabled friend test
     */
    public function testAddDisabledFriendAction()
    {
        $client = $this->getUserLoggedClient('user1', 'user1');
        $crawler = $client->request('GET', '/friends/searchToAdd');
        $form = $crawler->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler = $client->submit($form, array('search' => 'userDisabled'));
        $this->assertCount(1, $crawler->filter('body:contains("fulgurio.socialnetwork.add.no_user_found")'));
    }

    /**
     * Add friend test
     */
    public function testAddExistingFriendAction()
    {
        $client = $this->getUserLoggedClient('user1', 'user1');
        $crawler = $client->request('GET', '/friends/searchToAdd');
        $form = $crawler->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler = $client->submit($form, array('search' => 'user3'));
        $this->assertCount(1, $crawler->filter('body:contains("fulgurio.socialnetwork.add.no_user_found")'));
    }

    /**
     * Add wildcard friends test
     */
    public function testAddWildcardFriendAction()
    {
        $client = $this->getUserLoggedClient('user1', 'user1');
        $crawler = $client->request('GET', '/friends/searchToAdd');
        $form = $crawler->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler = $client->submit($form, array('search' => 'user'));
        // 2 users availables + 1 pending
        $this->assertCount(3, $crawler->filter('ol.addFriendsList li'));
    }

    /**
     * Add wildcard friends test
     */
    public function testAddFriendAction()
    {
        $client = $this->getUserLoggedClient('user1', 'user1');
        $crawler = $client->request('GET', '/friends/searchToAdd');
        $formSearch = $crawler->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler = $client->submit($formSearch, array('search' => 'user2'));

        $this->assertCount(1, $crawler->filter('ol.addFriendsList li'));
        $formAdd = $crawler->filter('form[action$="friends/add"] button[name="addSubmit"]')->form();
        $formAdd['friends_id[]']->tick();
        $crawler = $client->submit($formAdd);

        $this->assertTrue($client->getResponse()->isRedirect('/friends/'));
        $crawler = $client->followRedirect();

        $this->assertCount(1, $crawler->filter('body:contains("fulgurio.socialnetwork.invitation.success_msg")'));
    }

    /**
     * "Accept a friend" workflow test
     */
    public function testAcceptFriendshipAction()
    {
        $client2 = $this->getUserLoggedClient('user2', 'user2');
        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));

        $client1 = $this->getUserLoggedClient('user1', 'user1');
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(1, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));
        $crawler1 = $client1->request('GET', '/friends/searchToAdd');
        $formSearch = $crawler1->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler1 = $client1->submit($formSearch, array('search' => 'user2'));
        $formAdd = $crawler1->filter('form[action$="friends/add"] button[name="addSubmit"]')->form();
        $formAdd['friends_id[]']->tick();
        $crawler1 = $client1->submit($formAdd);
        $crawler1 = $client1->followRedirect();
        $this->assertCount(2, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(1, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        $user1 = $client1->getContainer()->get('security.context')->getToken()->getUser();
        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(1, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(1, $crawler2->filter('ol.askingFriends li'));
        $acceptLink = $crawler2->filter('ol.askingFriends li a[href="/friends/' . $user1->getId() . '/accept"]')->link();
        $crawler2 = $client2->click($acceptLink);

        // One new friend on each user
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(2, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));
        $this->assertCount(1, $crawler2->filter('ol.myFriends li'));
    }

    /**
     * "Refuse a friend" workflow test
     */
    public function testRefuseFriendshipAction()
    {
        $client2 = $this->getUserLoggedClient('user2', 'user2');
        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));

        $client1 = $this->getUserLoggedClient('user1', 'user1');
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(1, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));
        $crawler1 = $client1->request('GET', '/friends/searchToAdd');
        $formSearch = $crawler1->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler1 = $client1->submit($formSearch, array('search' => 'user2'));
        $formAdd = $crawler1->filter('form[action$="friends/add"] button[name="addSubmit"]')->form();
        $formAdd['friends_id[]']->tick();
        $crawler1 = $client1->submit($formAdd);
        $crawler1 = $client1->followRedirect();
        $this->assertCount(2, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(1, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        $user1 = $client1->getContainer()->get('security.context')->getToken()->getUser();
        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(1, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(1, $crawler2->filter('ol.askingFriends li'));
        $refuseLink = $crawler2->filter('ol.askingFriends li a[href="/friends/' . $user1->getId() . '/refuse"]')->link();
        $crawler2 = $client2->click($refuseLink);

        // We confirm refusal action
        $buttonYes = $crawler2->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client2->submit($form);

        // No more friend than begin
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(1, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));
        $this->assertCount(0, $crawler2->filter('ol.myFriends li'));
    }
}