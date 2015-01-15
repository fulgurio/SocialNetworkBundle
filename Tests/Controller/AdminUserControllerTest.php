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
     * Users list test
     */
    public function testlistAction()
    {
        $client = $this->getAdminLoggedClient();

        $crawler = $client->request('GET', '/admin/users/');
        $this->assertCount(
                2,
                $crawler->filter('table tbody tr')
        );
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

        $buttonNo = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonNo->form();
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

        $buttonNo = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonNo->form();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $secondLine = $crawler->filter('table tbody tr:contains(user2)')->first();

        $this->assertCount(1, $secondLine->filter('a[href$="/ban"]'));
    }
}