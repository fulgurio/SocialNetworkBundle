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
        $currentUser = $this->getUser();
        $className = $this->container->getParameter('fulgurio_social_network.user.group.class');
        $groups = $this->getDoctrine()
                ->getRepository($className)
                ->getUserMessengerListQuery($currentUser->getId())
                ->getResult();
        return $this->render(
                'FulgurioSocialNetworkBundle:MessengerList:list.html.twig',
                array(
                    'groups' => $groups
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
        $request = $this->get('request');
        $currentUser = $this->getUser();
        $userClassName = $this->container->getParameter('fos_user.model.user.class');
        $userRepo = $this->getDoctrine()->getRepository($userClassName);
        $options = array();
        if (!is_null($groupId))
        {
            $group = $this->getGroup($groupId);
            $options['selectedUsers'] = $group->getUsers();
        }
        else
        {
            $userGroupClassName = $this->container->getParameter('fulgurio_social_network.user.group.class');
            $group = new $userGroupClassName();
            $group->setTypeOfGroup('messengerList');
            $group->setOwner($currentUser);
            $users = $this->getRequest()->get('users');
            if ($users)
            {
                $options['selectedUsers'] = $userRepo->findBy(array('id' => $users));
            }
            else
            {
                $options['selectedUsers'] = array();
            }
        }
        $options['group'] = $group;
        $form = $this->createForm(new NewListFormType(), $group);
        $options['form'] = $form->createView();
        $formHandler = new NewListFormHandler(
                $form,
                $this->getRequest(),
                $this->get('translator')
        );
        if ($formHandler->process(
                $this->getDoctrine(),
                $options['selectedUsers'],
                $userClassName))
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
//            {
//                foreach ($form->getData()->idUsers as $userId)
//                {
//                    $hadUser = TRUE;
//                    foreach ($options['selectedUsers'] as $user)
//                    {
//                        if ($user->getId() == $userId)
//                        {
//                            $hadUser = FALSE;
//                            break;
//                        }
//                    }
//                    if ($hadUser)
//                    {
//                        $options['selectedUsers'][] = $userRepo->findOneById($userId);
//                    }
//                }
//            }
//        }
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
            return $this->redirect($this->generateUrl('fulgurio_social_network_messenger_list_remove'));
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
