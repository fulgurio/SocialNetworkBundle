<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Controller;

use FOS\UserBundle\Controller\ResettingController as Controller;
use FOS\UserBundle\Model\UserInterface;

/**
 * Controller resetting pages
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class ResettingController extends Controller
{
    /**
     * (non-PHPdoc)
     * @see FOS\UserBundle\Controller\ResettingController:getRedirectionUrl()
     */
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('fulgurio_social_network_homepage');
    }
}