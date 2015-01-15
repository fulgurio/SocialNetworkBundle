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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Users admin controller
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class AdminUserController extends Controller
{
    /**
     * Users listing action
     *
     * @return Response
     */
    public function listAction()
    {
        $request = $this->get('request');
        $users = $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:User')
                ->findOnlySubscribers(
                        $this->get('knp_paginator'),
                        $request->query->get('page', 1)
        );
        return $this->render(
                'FulgurioSocialNetworkBundle:AdminUsers:list.html.twig',
                array(
                    'users' => $users,
                )
        );
    }

    /**
     * Users view action
     *
     * @param number $userId
     * @return Response
     */
    public function viewAction($userId)
    {
        $user = $this->getSpecifiedUser($userId);
        return $this->render(
                'FulgurioSocialNetworkBundle:AdminUsers:view.html.twig',
                array(
                    'user' => $user,
                )
        );
    }

    /**
     * Users ban or unban action
     *
     * @todo : XmlRequest ?
     * @todo: back to initial user page (with pagination)
     * @param number $userId
     * @return Response
     */
    public function banAction($userId)
    {
        $request = $this->container->get('request');
        $user = $this->getSpecifiedUser($userId);
        $isEnabled = $user->isEnabled();
        if ($request->get('confirm') === 'yes')
        {
            $user->setEnabled(!$user->isEnabled());
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();
            $this->container->get('session')->setFlash(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.' . ($isEnabled ? 'ban' : 'unban') . '.success',
                            array('%username%' => $user->getUsername()),
                            'admin_user'
                    )
            );
            return new RedirectResponse($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        else if ($request->request->get('confirm') === 'no')
        {
            return new RedirectResponse($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Admin:confirm.html.twig',
                array(
                    'confirmationMessage' => $this->get('translator')->trans(
                            'fulgurio.socialnetwork.' . ($isEnabled ? 'ban' : 'unban') . '.confirm',
                            array(
                                '%username%' => $user->getUsername()
                            ),
                            'admin_user'
                    )
                )
        );
    }

    /**
     * Get user from given ID, and ckeck if he exists
     *
     * @param integer $userId
     * @throws NotFoundHttpException
     * @param number $userId
     * @return User
     */
    private function getSpecifiedUser($userId) {
        if (!$user = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User')->find($userId))
        {
            throw new NotFoundHttpException(
                $this->get('translator')->trans('fulgurio.socialnetwork.user_not_found', array(), 'admin_user')
            );
        }
        return ($user);
    }
}