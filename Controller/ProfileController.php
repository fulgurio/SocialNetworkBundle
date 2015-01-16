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

use FOS\UserBundle\Controller\ProfileController as Controller;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller profile pages
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class ProfileController extends Controller
{
    /**
     * Unsubscribe action
     */
    public function unsubscribeAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface)
        {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $request = $this->container->get('request');
        if ($request->get('confirm') === 'yes')
        {
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->deleteUser($user);
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_logout'));
        }
        else if ($request->get('confirm') === 'no')
        {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_profile_show'));
        }
        return $this->container->get('templating')->renderResponse(
                'FulgurioSocialNetworkBundle::confirm.html.twig',
                array(
                    'confirmationMessage' => $this->container->get('translator')->trans(
                            'fulgurio.socialnetwork.profile.unsubscribe.confirm'
                    )
                )
        );
    }
}