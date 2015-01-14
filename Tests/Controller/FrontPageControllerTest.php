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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Front page controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class FrontPageControllerTest extends WebTestCase
{
    /**
     * Homepage test
     */
    public function testHomepageAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertCount(
                1,
                $crawler->filter('h1:contains("fulgurio.socialnetwork.homepage")')
        );
    }
}