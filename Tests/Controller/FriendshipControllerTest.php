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
        $client2 = $this->getUserLoggedClient('user2', 'user2');
        $user2 = $client2->getContainer()->get('security.token_storage')->getToken()->getUser();
        $messengerExtension = $client2->getContainer()->get('fulgurio_social_network.twig.messenger_extension');
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user2));

        $client1 = $this->getUserLoggedClient('user1', 'user1');
        $user1 = $client1->getContainer()->get('security.token_storage')->getToken()->getUser();
        $crawler = $client1->request('GET', '/friends/searchToAdd');
        $formSearch = $crawler->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler = $client1->submit($formSearch, array('search' => 'user2'));

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user2));

        $client1->enableProfiler();
        $this->assertCount(1, $crawler->filter('ol.addFriendsList li'));
        $formAdd = $crawler->filter('form[action$="friends/add"] button[name="addSubmit"]')->form();
        $formAdd->setValues(array('friends_id' => array($user2->getId())));
        $crawler = $client1->submit($formAdd);

        // we check invitation email
        $mailCollector = $client1->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        $this->assertEquals('fulgurio.socialnetwork.invitation.email.subject', $message->getSubject());

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user2));

        $this->assertTrue($client1->getResponse()->isRedirect('/friends/'));
        $crawler = $client1->followRedirect();
        $this->assertCount(1, $crawler->filter('body:contains("fulgurio.socialnetwork.invitation.success_msg")'));
    }

    /**
     * "Accept a friend" workflow test
     */
    public function testAcceptFriendshipAction()
    {
        $client2 = $this->getUserLoggedClient('user2', 'user2');
        $user2 = $client2->getContainer()->get('security.token_storage')->getToken()->getUser();
        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));

        $client1 = $this->getUserLoggedClient('user1', 'user1');
        $user1 = $client1->getContainer()->get('security.token_storage')->getToken()->getUser();
        $messengerExtension = $client1->getContainer()->get('fulgurio_social_network.twig.messenger_extension');
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(6, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user2));

        $crawler1 = $client1->request('GET', '/friends/searchToAdd');
        $formSearch = $crawler1->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler1 = $client1->submit($formSearch, array('search' => 'user2'));
        $formAdd = $crawler1->filter('form[action$="friends/add"] button[name="addSubmit"]')->form();
        $formAdd->setValues(array('friends_id' => array($user2->getId())));
        $crawler1 = $client1->submit($formAdd);
        $crawler1 = $client1->followRedirect();
        $this->assertCount(7, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(1, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user2));

        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertCount(1, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(1, $crawler2->filter('ol.askingFriends li'));
        $acceptLink = $crawler2->filter('ol.askingFriends li a[href="/friends/' . $user1->getId() . '/accept"]')->link();
        $client2->enableProfiler();
        $crawler2 = $client2->click($acceptLink);

        // we check accept email
        $mailCollector2 = $client2->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector2->getMessageCount());
        $collectedMessages = $mailCollector2->getMessages();
        $message = $collectedMessages[0];
        $this->assertEquals('fulgurio.socialnetwork.accept.email.subject', $message->getSubject());

        // Messenger message
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user2));

        // One new friend on each user
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(7, $crawler1->filter('ol.myFriends li'));
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
        $user2 = $client2->getContainer()->get('security.token_storage')->getToken()->getUser();
        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));

        $client1 = $this->getUserLoggedClient('user1', 'user1');
        $user1 = $client1->getContainer()->get('security.token_storage')->getToken()->getUser();
        $messengerExtension = $client1->getContainer()->get('fulgurio_social_network.twig.messenger_extension');
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(6, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user2));

        $crawler1 = $client1->request('GET', '/friends/searchToAdd');
        $formSearch = $crawler1->filter('form[action$="friends/searchToAdd"] button[name="searchSubmit"]')->form();
        $crawler1 = $client1->submit($formSearch, array('search' => 'user2'));
        $formAdd = $crawler1->filter('form[action$="friends/add"] button[name="addSubmit"]')->form();
        $formAdd->setValues(array('friends_id' => array($user2->getId())));
        $crawler1 = $client1->submit($formAdd);
        $crawler1 = $client1->followRedirect();
        $this->assertCount(7, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(1, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user2));

        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(1, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(1, $crawler2->filter('ol.askingFriends li'));
        $refuseLink = $crawler2->filter('ol.askingFriends li a[href="/friends/' . $user1->getId() . '/refuse"]')->link();
        $crawler2 = $client2->click($refuseLink);

        // We confirm refusal action
        $buttonYes = $crawler2->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client2->enableProfiler();
        $client2->submit($form);

        // we check accept email
        $mailCollector2 = $client2->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector2->getMessageCount());
        $collectedMessages = $mailCollector2->getMessages();
        $message = $collectedMessages[0];
        $this->assertEquals('fulgurio.socialnetwork.refuse.email.subject', $message->getSubject());

        // Messenger message
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user2));

        // No more friend than begin
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(6, $crawler1->filter('ol.myFriends li'));
        $this->assertCount(0, $crawler1->filter('ol.myFriends li span:contains("fulgurio.socialnetwork.pending")'));

        $crawler2 = $client2->request('GET', '/friends/');
        $this->assertCount(0, $crawler2->filter('body:contains("fulgurio.socialnetwork.invitation.asking")'));
        $this->assertCount(0, $crawler2->filter('ol.askingFriends li'));
        $this->assertCount(0, $crawler2->filter('ol.myFriends li'));
    }

    /**
     * "Remove a friend" workflow test
     */
    public function testRemoveFriendshipAction()
    {
        $client3 = $this->getUserLoggedClient('user3', 'user3');
        $user3 = $client3->getContainer()->get('security.token_storage')->getToken()->getUser();
        $crawler3 = $client3->request('GET', '/friends/');
        $this->assertCount(1, $crawler3->filter('ol.myFriends li'));

        $client1 = $this->getUserLoggedClient('user1', 'user1');
        $user1 = $client1->getContainer()->get('security.token_storage')->getToken()->getUser();
        $messengerExtension = $client1->getContainer()->get('fulgurio_social_network.twig.messenger_extension');
        $crawler1 = $client1->request('GET', '/friends/');

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user3));

        $this->assertCount(6, $crawler1->filter('ol.myFriends li'));
        $removeLink = $crawler1->filter('ol.myFriends li a[href="/friends/' . $user3->getId() . '/refuse"]')->link();
        $crawler1 = $client1->click($removeLink);

        // We confirm refusal action
        $client1->enableProfiler();
        $buttonYes = $crawler1->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client1->submit($form);

        // we check accept email
        $mailCollector1 = $client1->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector1->getMessageCount());
        $collectedMessages = $mailCollector1->getMessages();
        $message = $collectedMessages[0];
        $this->assertEquals('fulgurio.socialnetwork.remove.email.subject', $message->getSubject());

        // Messenger message
        $this->assertEquals(0, $messengerExtension->nbOfUnreadMessage($user1));
        $this->assertEquals(1, $messengerExtension->nbOfUnreadMessage($user3));

        // No more friend than begin
        $crawler1 = $client1->request('GET', '/friends/');
        $this->assertCount(5, $crawler1->filter('ol.myFriends li'));

        $crawler3 = $client3->request('GET', '/friends/');
        $this->assertCount(0, $crawler3->filter('ol.myFriends li'));
    }
}