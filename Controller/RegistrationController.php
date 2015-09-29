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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\UserBundle\Controller\RegistrationController as Controller;
use FOS\UserBundle\Model\UserInterface;

/**
 * Controller registration page
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class RegistrationController extends Controller
{
    public function registerAction()
    {
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            // We redirect to homepage
            return new RedirectResponse($this->container->get('router')->generate('fulgurio_social_network_homepage'));
        }
        return parent::registerAction();
    }

    /**
     * @see FOS\UserBundle\Controller\RegistrationController::confirmedAction()
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface)
        {
            throw new AccessDeniedHttpException('This user does not have access to this section.');
        }
        // We set an notice flash and an email
        $this->container->get('fulgurio_social_network.fos_mailer')->sendRegistrationEmailMessage($user);
        $this->container->get('session')->getFlashBag()->add(
                'notice',
                'fulgurio.socialnetwork.register.welcome_msg'
        );
        // We redirect to homepage
        return new RedirectResponse($this->container->get('router')->generate('fulgurio_social_network_homepage'));
    }
}