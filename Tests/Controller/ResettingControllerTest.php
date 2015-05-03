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
 * Resetting controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class ResettingControllerTest extends WebTestCase
{
    /**
     * Reset password page test
     */
    public function testRequestAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $this->assertEquals('fulgurio.socialnetwork.lost_password.legend', $crawler->filter('form[action$="resetting/send-email"] legend')->text());
    }

    /**
     * Reset password empty form test
     */
    public function testSendEmailWithEmpyFormAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();

        $data = array('username' => '');
        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fulgurio.socialnetwork.lost_password.email_not_found")'));
    }

    /**
     * Reset password with wrong email form test
     */
    public function testSendEmailWithWrongEmailAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();

        $data = array('username' => 'userNotExits@example.com');
        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fulgurio.socialnetwork.lost_password.email_not_found")'));
    }

    /**
     * Sent resetting email test
     */
    public function testSendEmailAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();

        $data = array('username' => 'user1@example.com');
        $client->enableProfiler();
        $crawler = $client->submit($form, $data);

        $this->assertTrue($client->getResponse()->isRedirect('/resetting/check-email'));

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an e-mail was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting e-mail data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('resetting.email.subject', $message->getSubject());
        $this->assertEquals('webmaster@example.com', key($message->getFrom()));
        $this->assertEquals('user1@example.com', key($message->getTo()));
        $this->assertEquals('resetting.email.message', $message->getBody());
    }

    /**
     * 2 times sent resetting email test
     */
    public function testSendEmailAlreadySendAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();
        $data = array('username' => 'user1@example.com');
        $crawler = $client->submit($form, $data);

        // We try again, with same user
        $crawler = $client->request('GET', '/resetting/request');
        $crawler = $client->submit($form, $data);
        $this->assertEquals('resetting.password_already_requested', $crawler->filter('div.alert.alert-error')->text());
    }

    /**
     * Reinit password with empty form test
     */
    public function testCheckEmailWithEmptyFormAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();
        $data = array('username' => 'user1@example.com');
        $crawler = $client->submit($form, $data);

        $container = $client->getContainer();
        $user = $container->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('email' => $data['username']));
        $url = $container->get('router')->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()));

        $crawler = $client->request('GET', $url);
        $newData = array(
            'fos_user_resetting_form[new][first]' => '',
            'fos_user_resetting_form[new][second]' => ''
        );
        $form = $crawler->filter('form[action$="' . $url . '"] button[type="submit"]')->form();
        $crawler = $client->submit($form, $newData);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.new_password.blank")'));
        $security = $client->getContainer()->get('security.context');
        $this->assertFalse($security->isGranted('ROLE_USER'));
    }

    /**
     * Reinit password with empty form test
     */
    public function testCheckEmailWithBadValuesFormAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();
        $data = array('username' => 'user1@example.com');
        $crawler = $client->submit($form, $data);

        $container = $client->getContainer();
        $user = $container->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('email' => $data['username']));
        $url = $container->get('router')->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()));

        $crawler = $client->request('GET', $url);
        $newData = array(
            'fos_user_resetting_form[new][first]' => 'password1',
            'fos_user_resetting_form[new][second]' => 'password2'
        );
        $form = $crawler->filter('form[action$="' . $url . '"] button[type="submit"]')->form();
        $crawler = $client->submit($form, $newData);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fulgurio.socialnetwork.lost_password.password_no_match")'));
        $security = $client->getContainer()->get('security.context');
        $this->assertFalse($security->isGranted('ROLE_USER'));
    }

    /**
     * Reset password test
     */
    public function testResetAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/resetting/request');
        $form = $crawler->filter('form[action$="resetting/send-email"] button[type="submit"]')->form();
        $data = array('username' => 'user1@example.com');
        $crawler = $client->submit($form, $data);

        $container = $client->getContainer();
        $user = $container->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('email' => $data['username']));
        $url = $container->get('router')->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()));

        $crawler = $client->request('GET', $url);
        $newData = array(
            'fos_user_resetting_form[new][first]' => 'password1',
            'fos_user_resetting_form[new][second]' => 'password1'
        );
        $form = $crawler->filter('form[action$="' . $url . '"] button[type="submit"]')->form();
        $client->submit($form, $newData);
        $this->assertTrue($client->getResponse()->isRedirect('/'));
        $crawler = $client->followRedirect();

        $security = $client->getContainer()->get('security.context');
        $this->assertTrue($security->isGranted('IS_AUTHENTICATED_REMEMBERED'));
    }
}