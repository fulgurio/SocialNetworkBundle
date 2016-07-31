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

use Fulgurio\SocialNetworkBundle\Form\Handler\MessengerList\NewListFormHandler;
use Fulgurio\SocialNetworkBundle\Form\Type\MessengerList\NewListFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessengerListController extends Controller
{
    /**
     * List group page action
     *
     * @return Response
     */
    public function listAction()
    {
        if (FALSE == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            throw new AccessDeniedException();
        }
        $currentUser = $this->getUser();
        $userGroupClassName = $this->container->getParameter('fulgurio_social_network.user.group.class');
        $groups = $this->getDoctrine()
                ->getRepository($userGroupClassName)
                ->getUserMessengerListQuery($currentUser)
                ->getResult();
        return $this->render(
                'FulgurioSocialNetworkBundle:MessengerList:list.html.twig',
                array(
                    'groups' => $groups
                )
        );
    }

    /**
     * Messenger list show page
     *
     * @param number $groupId
     * @return Response
     */
    public function showAction($groupId)
    {
        if (FALSE == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            throw new AccessDeniedException();
        }
        //@todo : template
        $group = $this->getGroup($groupId);
        return $this->render(
                'FulgurioSocialNetworkBundle:MessengerList:show.html.twig',
                array(
                    'group' => $group,
                    'groupUsers' => $group->getUsers()
                )
        );
    }

    /**
     * Add or edit group page
     *
     * @param integer $groupId
     * @return Response
     */
    public function addAction($groupId = NULL)
    {
        if (FALSE == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            throw new AccessDeniedException();
        }
        $request = $this->get('request');
        $currentUser = $this->getUser();
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        $options = array();
        if (!is_null($groupId))
        {
            $group = $this->getGroup($groupId);
        }
        else
        {
            $userGroupClassName = $this->container->getParameter('fulgurio_social_network.user.group.class');
            $group = new $userGroupClassName();
            $group->setTypeOfGroup('messengerList');
            $group->setOwner($currentUser);
        }
        $options['group'] = $group;
        $formType = new NewListFormType(
                $currentUser,
                $this->getDoctrine(),
                $userClassName,
                $this->container->getParameter('fulgurio_social_network.friendship.class')
        );
        $form = $this->createForm($formType, $group);
        $formHandler = new NewListFormHandler(
                $form,
                $this->getRequest(),
                $this->get('translator')
        );
        if ($formHandler->process($this->getDoctrine()))
        {
            $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans(
                            'fulgurio.socialnetwork.' . (is_null($groupId) ? 'add' : 'edit') . '.success_msg',
                            array(),
                            'messenger-list'));
            $redirectUrl = $this->generateUrl('fulgurio_social_network_messenger_list_index');
            if ($request->isXmlHttpRequest())
            {
                return new JsonResponse(array(
                    'success' => 1,
                    'redirect' => $redirectUrl
                ));
            }
            return $this->redirect($redirectUrl);
        }
        elseif ($request->isXmlHttpRequest() && $formHandler->hasError())
        {
            return new JsonResponse(array('errors' => $formHandler->getErrors()));
        }
        $options['form'] = $form->createView();
        return $this->render('FulgurioSocialNetworkBundle:MessengerList:new.html.twig', $options);
    }

    /**
     * Messenger list remove page
     *
     * @param integer $groupId
     * @return Response
     */
    public function removeAction($groupId)
    {
        if (FALSE == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            throw new AccessDeniedException();
        }
        $request = $this->get('request');
        $group = $this->getGroup($groupId);
        if ($request->get('confirm'))
        {
            if ($request->get('confirm') === 'yes')
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->remove($group);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans(
                                'fulgurio.socialnetwork.remove.success_msg',
                                array(),
                                'messenger-list'
                        )
                );
            }
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list_index'));
        }
        $templateName = 'FulgurioSocialNetworkBundle::confirm' . ($request->isXmlHttpRequest() ? 'Ajax' : '') . '.html.twig';
        return $this->render($templateName, array(
                'action' => $this->generateUrl(
                        'fulgurio_social_network_messenger_list_remove',
                        array('groupId' => $groupId)
                ),
                'title' => $this->get('translator')->trans('fulgurio.socialnetwork.remove.title', array(), 'messenger-list'),
                'confirmationMessage' => $this->get('translator')->trans('fulgurio.socialnetwork.remove.confirm_msg', array(), 'messenger-list')
        ));
    }

    /**
     * Remove a user from a group
     *
     * @param number $groupId
     * @param number $userId
     * @return Response
     * @throws AccessDeniedException
     */
    public function removeOneUserAction($groupId, $userId)
    {
        if (FALSE == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            throw new AccessDeniedException();
        }
        $request = $this->get('request');
        $group = $this->getGroup($groupId);
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        $user = $this->getDoctrine()->getRepository($userClassName)->find($userId);
        if ($request->get('confirm'))
        {
            if ($request->get('confirm') === 'yes')
            {
                $em = $this->getDoctrine()->getEntityManager();
                $group->removeUser($user);
                $em->persist($group);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'success',
                        $this->get('translator')->trans(
                                'fulgurio.socialnetwork.remove_user.success_msg',
                                array(),
                                'messenger-list'
                        )
                );
            }
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list_show', array('groupId' => $groupId)));
        }
        $templateName = 'FulgurioSocialNetworkBundle::confirm' . ($request->isXmlHttpRequest() ? 'Ajax' : '') . '.html.twig';
        return $this->render($templateName, array(
                'action' => $this->generateUrl(
                        'fulgurio_social_network_messenger_user_list_remove',
                        array('groupId' => $groupId, 'userId' => $userId)
                ),
                'title' => $this->get('translator')->trans('fulgurio.socialnetwork.remove_user.title', array('%USERNAME%' => $user), 'messenger-list'),
                'confirmationMessage' => $this->get('translator')->trans('fulgurio.socialnetwork.remove_user.confirm_msg', array('%USERNAME%' => $user), 'messenger-list')
        ));
    }

    /**
     * Get group and check if current user is the owner
     *
     * @param number $groupId
     * @throws NotFoundHttpException
     * @return UserGroup
     */
    protected function getGroup($groupId)
    {
        $currentUser = $this->getUser();
        $userGroupClassName = $this->container->getParameter('fulgurio_social_network.user.group.class');
        $group = $this->getDoctrine()->getRepository($userGroupClassName)
                ->findOneBy(array(
                    'id' => $groupId,
                    'owner' => $currentUser->getId())
        );
        if (!$group)
        {
            throw new NotFoundHttpException(
                    $this->get('translator')->trans('No group found.')
            );
        }
        return $group;
    }
}
