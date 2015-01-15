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

use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    /**
     * Test setup
     */
    public function setUp()
    {
        // add all your doctrine fixtures classes
        $classes = array(
            'Fulgurio\SocialNetworkBundle\Tests\DataFixtures\ORM\LoadUsersData'
        );
        $this->loadFixtures($classes);
    }

    /**
     * Get a admin logged client
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function getAdminLoggedClient()
    {
        $data = array(
            '_username' => 'admin',
            '_password' => 'admin'
        );
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->filter('form[action$="login_check"].form-horizontal button[type="submit"]')->form();
        $client->submit($form, $data);
        return $client;
    }
}