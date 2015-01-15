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
 * Controller admin pages
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class AdminController extends Controller
{
    /**
     * Index page action
     */
    public function indexAction()
    {
        return $this->render('FulgurioSocialNetworkBundle:Admin:index.html.twig');
    }
}