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
 * Profil controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class ProfileControllerTest extends WebTestCase
{
    /**
     * User data
     * @var array
     */
    private $userData = array(
        'username' => 'user1',
        'email' => 'user1@example.com',
        'password' => 'user1'
    );

    /**
     * Show profil page test
     */
    public function testShowAction()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/profile/');
        $this->assertTrue($client->getResponse()->isRedirect('/login'));

        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->request('GET', '/profile/');
        $this->assertEquals('fulgurio.socialnetwork.profile.username: ' . $this->userData['username'], $crawler->filter('section p')->first()->text());
        $this->assertEquals('fulgurio.socialnetwork.profile.email: ' . $this->userData['email'], $crawler->filter('section p:nth-child(3)')->text());
    }

    /**
     * Edit profil page test
     */
    public function testEditWithEmptyFormAction()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/profile/');
        $this->assertTrue($client->getResponse()->isRedirect('/login'));

        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->request('GET', '/profile/edit');

        $data = array(
            'fos_user_profile_form[username]' => '',
            'fos_user_profile_form[email]' => '',
            'fos_user_profile_form[current_password]' => ''
        );
        $form = $crawler->filter('form[action$="profile/edit"] button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.username.blank")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.blank")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("This value should be the user current password.")'));
    }

    /**
     * Edit profil page test
     */
    public function testEditWithExistingUserAction()
    {
        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->request('GET', '/profile/edit');

        $data = array(
            'fos_user_profile_form[username]' => 'userDisabled',
            'fos_user_profile_form[email]' => 'userDisabled@example.com',
            'fos_user_profile_form[current_password]' => $this->userData['password']
        );
        $form = $crawler->filter('form[action$="profile/edit"] button[name="_submit"]')->form();

        $crawler = $client->submit($form, $data);
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.username.already_used")'));
        $this->assertCount(1, $crawler->filter('div.alert.alert-error:contains("fos_user.email.already_used")'));
    }

    /**
     * Edit profil page test
     */
    public function testEditWithoutPasswordChangeAction()
    {
        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->request('GET', '/profile/edit');

        $data = array(
            'fos_user_profile_form[username]' => 'foobar',
            'fos_user_profile_form[email]' => 'foobar@example.com',
            'fos_user_profile_form[plainPassword][first]' => '',
            'fos_user_profile_form[plainPassword][second]' => '',
            'fos_user_profile_form[current_password]' => $this->userData['password']
        );
        $userBeforeSave = $client->getContainer()->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('username' => $this->userData['username']));
        $form = $crawler->filter('form[action$="profile/edit"] button[name="_submit"]')->form();

        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('fulgurio.socialnetwork.profile.username: ' . $data['fos_user_profile_form[username]'], $crawler->filter('section p')->first()->text());
        $this->assertEquals('fulgurio.socialnetwork.profile.email: ' . $data['fos_user_profile_form[email]'], $crawler->filter('section p:nth-child(3)')->text());
        $userAfterSave = $client->getContainer()->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('username' => $data['fos_user_profile_form[username]']));
        $this->assertEquals($userBeforeSave->getPassword(), $userAfterSave->getPassword());
    }

    /**
     * Edit profil page test
     */
    public function testEditAction()
    {
        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->request('GET', '/profile/edit');

        $userBeforeSave = $client->getContainer()->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('username' => $this->userData['username']));
        $data = array(
            'fos_user_profile_form[username]' => 'foobar',
            'fos_user_profile_form[email]' => 'foobar@example.com',
            'fos_user_profile_form[plainPassword][first]' => 'foobar',
            'fos_user_profile_form[plainPassword][second]' => 'foobar',
            'fos_user_profile_form[current_password]' => $this->userData['password']
        );
        $form = $crawler->filter('form[action$="profile/edit"] button[name="_submit"]')->form();

        $client->submit($form, $data);
        $crawler = $client->followRedirect();
        $this->assertEquals('fulgurio.socialnetwork.profile.username: ' . $data['fos_user_profile_form[username]'], $crawler->filter('section p')->first()->text());
        $this->assertEquals('fulgurio.socialnetwork.profile.email: ' . $data['fos_user_profile_form[email]'], $crawler->filter('section p:nth-child(3)')->text());
        $this->assertTrue('/bundles/fulguriosocialnetwork/images/avatar.png' === $crawler->filter('section img')->attr('src'));

        $userAfterSave = $client->getContainer()->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('username' => $data['fos_user_profile_form[username]']));
        $this->assertNotEquals($userBeforeSave->getPassword(), $userAfterSave->getPassword());

        $encoder = $client->getContainer()->get('security.encoder_factory')->getEncoder($userAfterSave);
        $encryptedPassword = $encoder->encodePassword($data['fos_user_profile_form[username]'], $userAfterSave->getSalt());

        $this->assertSame($encryptedPassword, $userAfterSave->getPassword());
    }

    /**
     * Edit profil with avatar upload page test
     */
    public function testEditWithUploadAction()
    {
        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->request('GET', '/profile/edit');

        $userBeforeSave = $client->getContainer()->get('doctrine')->getEntityManager()->getRepository('FulgurioSocialNetworkBundle:User')->findOneBy(array('username' => $this->userData['username']));
        $form = $crawler->filter('form[action$="profile/edit"] button[name="_submit"]')->form();
        $form['fos_user_profile_form[avatarFile]'] = __DIR__ . '/../DataFixtures/icon.png';
        $form['fos_user_profile_form[current_password]'] = $this->userData['password'];

        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals('fulgurio.socialnetwork.profile.username: ' . $this->userData['username'], $crawler->filter('section p')->first()->text());
        $this->assertEquals('fulgurio.socialnetwork.profile.email: ' . $this->userData['email'], $crawler->filter('section p:nth-child(3)')->text());
        $this->assertFalse('/bundles/fulguriosocialnetwork/images/avatar.png' === $crawler->filter('section img')->attr('src'));
    }

    /**
     * Unsubscribe page test
     */
    public function testUnsubscribeAction()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/profile/');
        $this->assertTrue($client->getResponse()->isRedirect('/login'));

        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $crawler = $client->followRedirect();

        // Authentified
        $security = $client->getContainer()->get('security.authorization_checker');
        $this->assertTrue($security->isGranted('ROLE_USER'));

        $crawler = $client->request('GET', '/unsubscribe');
        $buttonNo = $crawler->filter('a:contains("fulgurio.socialnetwork.no")')->link();
        $crawler = $client->click($buttonNo);
        $security = $client->getContainer()->get('security.authorization_checker');
        $this->assertTrue($security->isGranted('ROLE_USER'));

        $crawler = $client->request('GET', '/unsubscribe');
        $buttonYes = $crawler->selectButton('fulgurio.socialnetwork.yes');
        $form = $buttonYes->form();
        $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/logout'));
        $client->followRedirect();
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/'));
        $crawler = $client->followRedirect();

        $security = $client->getContainer()->get('security.authorization_checker');
        $this->assertFalse($security->isGranted('ROLE_USER'));

        // Try to reconnect
        $client = $this->getUserLoggedClient($this->userData['username'], $this->userData['password']);
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
        $client->followRedirect();

        $security = $client->getContainer()->get('security.authorization_checker');
        $this->assertFalse($security->isGranted('ROLE_USER'));
    }
}