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
 * Registration controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Register form access test
     */
    public function testRegisterAction()
    {
        $data = array(
            'fos_user_registration_form[username]'      => 'user100',
            'fos_user_registration_form[email]'         => 'user100@example.com',
            'fos_user_registration_form[plainPassword]' => 'user100',
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/register/');

        $this->assertEquals('fulgurio.socialnetwork.register.legend', $crawler->filter('form legend')->text());

        $form = $crawler->filter('form[action$="register/"] button[name="_submit"]')->form();
        $crawler = $client->submit($form, $data);
        $this->assertTrue($client->getResponse()->isRedirect('/register/confirmed'));

        // Authentified
        $security = $client->getContainer()->get('security.authorization_checker');
        $this->assertTrue($security->isGranted('ROLE_USER'));
    }

    /**
     * Register form with empty form test
     */
    public function testRegisterWithEmptyForm()
    {
        $data = array(
            'fos_user_registration_form[username]'      => '',
            'fos_user_registration_form[email]'         => '',
            'fos_user_registration_form[plainPassword]' => '',
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/register/');
        $form = $crawler->filter('form[action$="register/"] button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.username.blank")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.blank")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.password.blank")'));
    }

    /**
     * Register form with bad email test
     */
    public function testRegisterWithBadEmail()
    {
        $data = array(
            'fos_user_registration_form[username]'      => '',
            'fos_user_registration_form[email]'         => 'bademail',
            'fos_user_registration_form[plainPassword]' => '',
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/register/');
        $form = $crawler->filter('form[action$="register/"] button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.invalid")'));
    }

    /**
     * Register form with existing user
     */
    public function testRegisterWithExistingUser()
    {
        $data = array(
            'fos_user_registration_form[username]'      => 'user1',
            'fos_user_registration_form[email]'         => 'user1@example.com',
            'fos_user_registration_form[plainPassword]' => 'user1',
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/register/');
        $form = $crawler->filter('form[action$="register/"] button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.already_used")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.username.already_used")'));
    }
}
