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
 * Admin user controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class AdminUserControllerTest extends WebTestCase
{
    /**
     * Number of user with ROLE_USER into database
     */
    const NB_MEMBER = 3;

    /**
     * Users list test
     */
    public function testlistAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $this->assertCount(self::NB_MEMBER, $crawler->filter('table tbody tr'));
    }

    /**
     * Unknow user view action test
     */
    public function testViewUnknowUserAction()
    {
        $client = $this->getAdminLoggedClient();

        $client->request('GET', '/admin/users/0/view');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * User view action test
     */
    public function testViewAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr')->first();
        $link = $firstLine->filter('a[href$="/view"]')->link();
        $crawler = $client->click($link);

        $this->assertCount(1, $crawler->filter('p span:contains("fulgurio.socialnetwork.view.username")'));
        $this->assertCount(1, $crawler->filter('p span:contains("fulgurio.socialnetwork.view.email")'));
        $this->assertCount(1, $crawler->filter('p span:contains("fulgurio.socialnetwork.view.register_date")'));
        $this->assertCount(1, $crawler->filter('p span:contains("fulgurio.socialnetwork.view.last_login")'));
    }

    /**
     * User ban action test
     */
    public function testBanWithoutConfirmAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr')->first();

        $link = $firstLine->filter('a[href$="/ban"]')->link();
        $crawler = $client->click($link);

        $buttonNo = $crawler->selectButton('fulgurio.socialnetwork.no');
        $form = $buttonNo->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $firstLine = $crawler->filter('table tbody tr')->first();

        $this->assertCount(1, $firstLine->filter('a[href$="/ban"]'));
    }

    /**
     * User ban action test
     */
    public function testBanAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr:contains(user1)')->first();

        $link = $firstLine->filter('a[href$="/ban"]')->link();
        $crawler = $client->click($link);

        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $firstLine = $crawler->filter('table tbody tr')->first();

        $this->assertCount(1, $firstLine->filter('a[href$="/unban"]'));
    }

    /**
     * User unban action test
     */
    public function testUnbanAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $secondLine = $crawler->filter('table tbody tr:contains(user2)')->first();

        $link = $secondLine->filter('a[href$="/unban"]')->link();
        $crawler = $client->click($link);

        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $secondLine = $crawler->filter('table tbody tr:contains(user2)')->first();

        $this->assertCount(1, $secondLine->filter('a[href$="/ban"]'));
    }

    /**
     * User remove action test
     */
    public function testRemoveAction()
    {
        $client = $this->getSuperAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr:contains(user1)')->first();

        $link = $firstLine->filter('a[href$="/remove"]')->link();
        $crawler = $client->click($link);

        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertCount(self::NB_MEMBER - 1, $crawler->filter('table tbody tr'));
    }

    /**
     * Init password action test
     */
    public function testInitPasswordAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr')->first();
        $email = $firstLine->filter('td:nth-child(5)')->text();
        $link = $firstLine->filter('a[href$="/view"]')->link();
        $crawler = $client->click($link);
        $initPasswordLink = $crawler->filter('a[href$="/init-password"]')->link();
        $crawler = $client->click($initPasswordLink);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting e-mail data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('resetting.email.subject', $message->getSubject());
        $this->assertEquals('webmaster@example.com', key($message->getFrom()));
        $this->assertEquals($email, key($message->getTo()));
        $this->assertEquals('resetting.email.message', $message->getBody());
    }

    /**
     * Remove avatar action test
     */
    public function testRemoveAvatarAction()
    {
        $client = $this->getAdminLoggedClient();
        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr:contains(user3)')->first();
        $avatar = $firstLine->filter('img')->first();
        $this->assertNotEquals('/bundles/fulguriosocialnetwork/images/avatar.png', $avatar->attr('src'));
        $viewTag = $firstLine->filter('a[href$="/view"]');
        $crawler = $client->click($viewTag->link());
        $this->assertCount(1, $crawler->filter('a:contains("fulgurio.socialnetwork.actions.remove_avatar")'));
        $removeAvatarLink = $crawler->filter('a:contains("fulgurio.socialnetwork.actions.remove_avatar")')->link();

        $crawler = $client->click($removeAvatarLink);
        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $avatar = $crawler->filter('table tbody tr:contains(user3) img')->first();
        $this->assertEquals('/bundles/fulguriosocialnetwork/images/avatar.png', $avatar->attr('src'));
    }
}