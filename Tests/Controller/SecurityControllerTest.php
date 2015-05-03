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
 * Security controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Login access test
     */
    public function testLoginAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertCount(1, $crawler->filter('form legend:contains("fulgurio.socialnetwork.signin.legend")'));
    }

    /**
     * Empty login form test
     */
    public function testLoginAtionWithEmptyValue()
    {
        $data = array(
            '_username' => '',
            '_password' => ''
        );
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('Bad credentials', $crawler->filter('div.alert.alert-error')->text());
    }

    /**
     * Login form with unknow user test
     */
    public function testLoginActionWithUnknowUser()
    {
        $data = array(
            '_username' => 'unknowuser',
            '_password' => ''
        );
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('Bad credentials', $crawler->filter('div.alert.alert-error')->text());
        $security = $client->getContainer()->get('security.context');
        $this->assertFalse($security->isGranted('ROLE_USER'));
    }

    /**
     * Login form with bad password test
     */
    public function testLoginActionWithBadPassword()
    {
        $data = array(
            '_username' => 'user1',
            '_password' => 'badpassword'
        );
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('Bad credentials', $crawler->filter('div.alert.alert-error')->text());
        $security = $client->getContainer()->get('security.context');
        $this->assertFalse($security->isGranted('ROLE_USER'));
    }

    /**
     * Login form with disabled user test
     */
    public function testLoginActionWithDisabledUser()
    {
        $data = array(
            '_username' => 'user2',
            '_password' => 'user2'
        );
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('User account is disabled.', $crawler->filter('div.alert.alert-error')->text());
    }

    /**
     * Login form with existing user test (logged)
     */
    public function testLoginActionWithExistingUser()
    {
        $data = array(
            '_username' => 'user1',
            '_password' => 'user1'
        );
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $crawler = $client->submit($form, $data);
        // Authentified
        $security = $client->getContainer()->get('security.context');
        $this->assertTrue($security->isGranted('ROLE_USER'));
    }

    /**
     * Login test
     */
    public function testLogoutAction()
    {
        $data = array(
            '_username' => 'user1',
            '_password' => 'user1'
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $crawler = $client->submit($form, $data);
        // Authentified
        $security = $client->getContainer()->get('security.context');
        $this->assertTrue($security->isGranted('ROLE_USER'));

        $client->request('GET', '/logout');
        $crawler = $client->followRedirect();
        $security = $client->getContainer()->get('security.context');
        $this->assertFalse($security->isGranted('ROLE_USER'));
    }
}