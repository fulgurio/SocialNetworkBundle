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

use Fulgurio\SocialNetworkBundle\Entity\Admin\Contact;
use Fulgurio\SocialNetworkBundle\Form\Type\AdminContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        $search = trim($request->get('s', ''));
        $page = $request->get('page', 1);
        $repository = $this->getDoctrine()
                ->getRepository('FulgurioSocialNetworkBundle:User');
        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN'))
        {
            $users = $repository->findWithPagination(
                    $this->get('knp_paginator'),
                    $page,
                    $search);
        }
        else
        {
            $users = $repository->findOnlySubscribers(
                    $this->get('knp_paginator'),
                    $page,
                    $search);
        }
        if (count($users) == 0 && $page > 1)
        {
            return new RedirectResponse($this->generateUrl('fulgurio_social_network_admin_users', array('page' => $page - 1)));
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:AdminUsers:list.html.twig',
                array(
                    'users' => $users,
                    'searchQuery' => $search
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
     * Users remove action
     *
     * @todo : XmlRequest ?
     * @todo : back to initial user page (with pagination)
     */
    public function removeAction($userId)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            throw new AccessDeniedHttpException();
        }
        $user = $this->getSpecifiedUser($userId);
        $request = $this->getRequest();
        if ($request->get('confirm') === 'yes')
        {
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->deleteUser($user);
            $this->container->get('session')->setFlash(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.remove.success',
                            array('%username%' => $user->getUsername()),
                            'admin_user'
                    )
            );
            return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        else if ($request->get('confirm') === 'no')
        {
            return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Admin:confirm.html.twig',
                array(
                    'confirmationMessage' => $this->get('translator')->trans(
                            'fulgurio.socialnetwork.remove.confirm',
                            array('%username%' => $user->getUsername()),
                            'admin_user'
                    )
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
        $request = $this->getRequest();
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
            return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        else if ($request->get('confirm') === 'no')
        {
            return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Admin:confirm.html.twig',
                array(
                    'confirmationMessage' => $this->get('translator')->trans(
                            'fulgurio.socialnetwork.' . ($isEnabled ? 'ban' : 'unban') . '.confirm',
                            array('%username%' => $user->getUsername()),
                            'admin_user'
                    )
                )
        );
    }

    /**
     * Users contact action
     *
     * @todo : back to initial user page (with pagination)
     */
    public function contactAction($userId)
    {
        $user = $this->getSpecifiedUser($userId);
        $request = $this->getRequest();
        $form = $this->createForm(new AdminContactFormType(), new Contact());
        if ($request->getMethod() === 'POST')
        {
            $form->bindRequest($request);
            if ($form->isValid())
            {
                $data = $form->getData();
                $this->container->get('fulgurio_social_network.admin_mailer')
                        ->sendContactMessage(
                                $user,
                                $data->getSubject(),
                                $data->getMessage()
                );
                $this->container->get('session')->setFlash(
                        'notice',
                        $this->get('translator')->trans(
                                'fulgurio.socialnetwork.contact.success',
                                array(),
                                'admin_user'
                        )
                );
                return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
            }
        }
        return $this->render('FulgurioSocialNetworkBundle:AdminUsers:contact.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Init user password
     *
     * @param number $userId
     * @return Response
     */
    public function initPasswordAction($userId)
    {
        $user = $this->getSpecifiedUser($userId);
        $user->generateConfirmationToken();
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);
        $this->container->get('session')->setFlash(
                'success',
                $this->get('translator')->trans(
                        'fulgurio.socialnetwork.password_init.success',
                        array('%email%' => $user->getEmail()),
                        'admin_user'
                )
        );
        return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
    }

    /**
     * Remove user avatar
     *
     * @param number $userId
     * @todo : XmlRequest ?
     * @todo : back to initial user page (with pagination)
     */
    public function removeAvatarAction($userId)
    {
        $user = $this->getSpecifiedUser($userId);
        if ($user->getAvatar() === NULL)
        {
            throw new AccessDeniedHttpException();
        }
        $request = $this->getRequest();
        if ($request->get('confirm') === 'yes')
        {
            //@todo: remove file ?
            $user->setAvatar(null);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();
                $this->container->get('fulgurio_social_network.admin_mailer')
                        ->sendRemovedAvatarMessage($user);
            $this->container->get('session')->setFlash(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.remove_avatar.success',
                            array('%username%' => $user->getUsername()),
                            'admin_user'
                    )
            );
            return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        else if ($request->get('confirm') === 'no')
        {
            return $this->redirect($this->generateUrl('fulgurio_social_network_admin_users'));
        }
        return $this->render(
                'FulgurioSocialNetworkBundle:Admin:confirm.html.twig',
                array(
                    'confirmationMessage' => $this->get('translator')->trans(
                            'fulgurio.socialnetwork.remove_avatar.confirm',
                            array('%username%' => $user->getUsername()),
                            'admin_user'
                    )
                )
        );
    }

    /**
     * Get user from given ID, and ckeck if he exists
     *
     * @throws NotFoundHttpException
     * @param number $userId
     * @return User
     */
    private function getSpecifiedUser($userId)
    {
        if (!$user = $this->getDoctrine()->getRepository('FulgurioSocialNetworkBundle:User')->find($userId))
        {
            throw new NotFoundHttpException(
                $this->get('translator')->trans('fulgurio.socialnetwork.user_not_found', array(), 'admin_user')
            );
        }
        return ($user);
    }
}