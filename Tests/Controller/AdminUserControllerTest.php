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
     * Pagination
     */
    const PAGINATION_LIMIT = 10;

    /**
     * Number of user with ROLE_USER into database
     */
    const NB_MEMBER = 11;

    /**
     * Users list test
     */
    public function testlistAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $this->assertCount(self::NB_MEMBER > self::PAGINATION_LIMIT ? self::PAGINATION_LIMIT : self::NB_MEMBER, $crawler->filter('table tbody tr'));
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
     * User add action test
     */
    public function testAddAction()
    {
        $client = $this->getSuperAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $nbUsers = $crawler->filter('table tbody tr')->count();
        $link = $crawler->filter('a[href$="/add"]')->link();
        $crawler = $client->click($link);

        $data = array(
            'user[username]' => 'foobar',
            'user[email]' => 'foobar@example.com',
            'user[newPassword][first]' => 'foobar',
            'user[newPassword][second]' => 'foobar',
        );
        $form = $crawler->filter('form[action$="/add"] button[type="submit"]')->form();
        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertCount($nbUsers >= self::PAGINATION_LIMIT ? self::PAGINATION_LIMIT : ($nbUsers + 1), $crawler->filter('table tbody tr'));
    }

    /**
     * User add action test
     */
    public function testEditAction()
    {
        $client = $this->getSuperAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $nbUsers = $crawler->filter('table tbody tr')->count();
        $firstLine = $crawler->filter('table tbody tr:contains(user2)')->first();

        $link = $firstLine->filter('a[href$="/edit"]')->link();
        $crawler = $client->click($link);

        $data = array(
            'user[email]' => 'user22@example.com',
        );
        $form = $crawler->filter('form[action$="/edit"] button[type="submit"]')->form();
        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertCount($nbUsers, $crawler->filter('table tbody tr'));
        $this->assertCount(1, $crawler->filter('table tbody tr:contains(user22) td:contains(user22)'));
    }

    /**
     * User remove action test
     */
    public function testRemoveAction()
    {
        $client = $this->getSuperAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $nbUsers = $crawler->filter('table tbody tr')->count();
        $firstLine = $crawler->filter('table tbody tr:contains(user1)')->first();

        $link = $firstLine->filter('a[href$="/remove"]')->link();
        $crawler = $client->click($link);

        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertCount($nbUsers >= self::PAGINATION_LIMIT ? self::PAGINATION_LIMIT : ($nbUsers - 1), $crawler->filter('table tbody tr'));
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

        $buttonNo = $crawler->filter('a:contains("fulgurio.socialnetwork.no")')->link();
        $crawler = $client->click($buttonNo);
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

        $crawler = $client->request('GET', '/admin/users/?page=2');
        $secondLine = $crawler->filter('table tbody tr:contains(userDisabled)')->first();

        $link = $secondLine->filter('a[href$="/unban"]')->link();
        $crawler = $client->click($link);

        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $secondLine = $crawler->filter('table tbody tr:contains(userDisabled)')->first();

        $this->assertCount(1, $secondLine->filter('a[href$="/ban"]'));
    }


    /**
     * User contact action test
     */
    public function testContactAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr:contains(user1)')->first();

        $link = $firstLine->filter('a[href$="/contact"]')->link();
        $crawler = $client->click($link);

        $data = array(
            'contact[subject]' => 'New message',
            'contact[message]' => 'this is a test'
        );
        $form = $crawler->filter('form[action$="contact"] button[type="submit"]')->form();
        $client->enableProfiler();
        $crawler = $client->submit($form, $data);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting e-mail data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals($data['contact[subject]'], $message->getSubject());
        $this->assertContains($data['contact[message]'], $message->getBody());
    }

    /**
     * Init password action test
     */
    public function testInitPasswordAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $firstLine = $crawler->filter('table tbody tr')->first();
        $email = $firstLine->filter('td:nth-child(3)')->text();
        $link = $firstLine->filter('a[href$="/view"]')->link();
        $crawler = $client->click($link);
        $initPasswordLink = $crawler->filter('a[href$="/init-password"]')->link();
        $client->enableProfiler();
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
        $firstLine = $crawler->filter('table tbody tr:contains(user2)')->first();
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
        $avatar = $crawler->filter('table tbody tr:contains(user2) img')->first();
        $this->assertEquals('/bundles/fulguriosocialnetwork/images/avatar.png', $avatar->attr('src'));
    }
}