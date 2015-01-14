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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller usual pages
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class FrontPageController extends Controller
{
    /**
     * Homepage
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function homepageAction()
    {
        return $this->render('FulgurioSocialNetworkBundle:FrontPage:homepage.html.twig', array());
    }
}
