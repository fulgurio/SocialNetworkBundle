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
     * Show page action
     */
    public function showAction($userId = null)
    {
        if (!$this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return new RedirectResponse($this->container->get('router')->generate('fos_user_security_login'));
        }
        $currentUser = $this->getUser();
        if (is_null($userId))
        {
            $userToDisplay = $currentUser;
        }
        else
        {
            $doctrine = $this->container->get('doctrine');
            $userClassName = $this->container->getParameter('fos_user.model.user.class');
            $userToDisplay = $doctrine->getRepository($userClassName)->find($userId);
            if ($currentUser != $userToDisplay && (!$userToDisplay->hasRole('ROLE_ADMIN') || !$userToDisplay->hasRole('ROLE_SUPER_ADMIN')))
            {
                throw new NotFoundHttpException();
            }
        }
        return $this->container->get('templating')->renderResponse(
                'FulgurioSocialNetworkBundle:Profile:show.html.twig',
                array('user' => $userToDisplay)
        );
    }

    /**
     * Unsubscribe action
     */
    public function unsubscribeAction()
    {
        $currentUser = $this->getUser();
        if (!$currentUser)
        {
            throw new AccessDeniedException();
        }
        $request = $this->container->get('request');
        if ($request->get('confirm'))
        {
            if ($request->get('confirm') === 'yes')
            {
                $userManager = $this->container->get('fos_user.user_manager');
                $userManager->deleteUser($currentUser);
                return new RedirectResponse($this->container->get('router')->generate('fos_user_security_logout'));
            }
            if ($request->get('referer'))
            {
                return new RedirectResponse($request->get('referer'));
            }
            return new RedirectResponse($this->container->get('router')->generate('fos_user_profile_show'));
        }
        $templateName = 'FulgurioSocialNetworkBundle::confirm' . ($request->isXmlHttpRequest() ? 'Ajax' : '') . '.html.twig';
        return $this->container->get('templating')->renderResponse(
                $templateName,
                array(
                    'url_referer' => $request->server->get('HTTP_REFERER'),
                    'action' => $this->container->get('router')->generate('fulgurio_social_network_unsubscribe'),
                    'confirmationMessage' => $this->container->get('translator')->trans(
                            'fulgurio.socialnetwork.profile.unsubscribe.confirm'
                    )
                )
        );
    }

    /**
     * Get current user
     *
     * @return type
     */
    private function getUser()
    {
        return $this->container->get('security.context')->getToken()->getUser();
    }
}