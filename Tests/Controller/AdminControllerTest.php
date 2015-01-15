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
 * Admin controller tests
 *
 * @author Vincent GUERARD <v.guerard@fulgurio.net>
 */
class AdminControllerTest extends WebTestCase
{
    /**
     * Admin index test
     */
    public function testIndexAction()
    {
        $client = $this->getAdminLoggedClient();

        $client->request('GET', '/admin/');
        // Authentified
        $security = $client->getContainer()->get('security.context');
        $this->assertTrue($security->isGranted('ROLE_ADMIN'));
    }
}