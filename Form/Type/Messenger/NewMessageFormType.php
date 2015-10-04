<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Form\Type\Messenger;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class NewMessageFormType extends AbstractType
{
    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User $currentUser
     */
    protected $currentUser;

    /**
     * @var Symfony\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $messageClassName;

    /**
     * @var string
     */
    protected $messageTargetClassName;

    /**
     * @var string
     */
    protected $userClassName;

    /**
     * @var string
     */
    protected $userFriendshipClassName;

    /**
     * @var string
     */
    protected $userGroupClassName;


    /**
     * Constructor
     *
     * @param User $securityContext
     * @param Registry $doctrine
     * @param string $messageClassName
     * @param string $messageTargetClassName
     * @param string $userClassName
     * @param string $userFriendshipClassName
     * @param string $userGroupClassName
     */
    public function __construct(SecurityContextInterface $securityContext, Registry $doctrine, $messageClassName, $messageTargetClassName, $userClassName, $userFriendshipClassName, $userGroupClassName)
    {
        $this->currentUser = $securityContext->getToken()->getUser();
        $this->doctrine = $doctrine;
        $this->messageClassName = $messageClassName;
        $this->messageTargetClassName = $messageTargetClassName;
        $this->userClassName = $userClassName;
        $this->userFriendshipClassName = $userFriendshipClassName;
        $this->userGroupClassName = $userGroupClassName;
    }

    /**
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentUser = $this->currentUser;
        $builder
            ->add('username_target', 'text', array(
                'required' => FALSE,
                'mapped' => FALSE,
            ))
            ->add('id_targets', 'entity', array(
                'multiple' => TRUE,
                'mapped' => FALSE,
                'class' => $this->userClassName,
                'query_builder' => function(UserRepository $er) use ($currentUser)
                {
                    return $er->getAcceptedFriendsQuery($currentUser);
                }
            ))
            ->addEventListener(FormEvents::POST_SUBMIT, array($this, 'checkTarget'))
           ->add('subject', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.new_message.subject.not_blank'))
                )
            ))
            ->add('content', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.new_message.content.not_blank'))
                )
            ))
            ->add('file', 'file', array('required' => FALSE))
            ;
            if ($this->userGroupClassName)
            {
                $builder->add('group', 'entity', array(
                    'class' => $this->userGroupClassName,
                    'choices'  => $this->doctrine
                        ->getRepository($this->userGroupClassName)
                        ->getUserMessengerListQuery($this->currentUser)
                        ->getResult(),
                    'property' => 'name',
                    'required' => FALSE,
                    'mapped'   => FALSE
                ));
            }
    }

    /**
     * Check targets value
     *
     * @param FormEvent $event
     */
    public function checkTarget(FormEvent $event)
    {
        $form = $event->getForm();
        $userRepository = $this->doctrine
                ->getRepository($this->userClassName);
        $idTargets = $form->get('id_targets');
        $usersId = (count($idTargets->getViewData()) > 0) ? $idTargets->getViewData() : array();
        $usernameTarget = $form->get('username_target');
        if (trim($usernameTarget->getData()) != '')
        {
            $usernames = preg_split('/[;,]/', strtolower($usernameTarget->getData()));
            $users = $userRepository->findBy(array('username' => $usernames));
            foreach ($users as $user)
            {
                if (!in_array($user->getId(), $usersId))
                {
                    $usersId[] = $user->getId();
                }
            }
        }
        if (!empty($usersId))
        {
            // Filter to get only friends
            $friends = $this->getOnlyFriends($usersId);
            $message = $form->getData();
            foreach ($friends as $friend)
            {
                $target = new $this->messageTargetClassName();
                $target->setMessage($message);
                $target->setTarget($friend);
                $this->doctrine->getManager()->persist($target);
                $message->addTarget($target);
            }
            return;
        }
        if ($form->has('group') && $form->get('group')->getData() != NULL)
        {
            return;
        }
        $usernameTarget->addError(new FormError('fulgurio.socialnetwork.new_message.no_friend_found'));
    }

    /**
     * Get friends from username typed value
     *
     * @param array $usersId
     * @return array
     */
    protected function getOnlyFriends($usersId)
    {
        $myFriends = $this->doctrine
                ->getRepository($this->userFriendshipClassName)
                ->findAcceptedFriends($this->currentUser);
        $foundedFriends = array();
        if (!empty($myFriends))
        {
            foreach ($myFriends as $myFriend)
            {
                foreach ($usersId as $id)
                {
                    if ($id == $myFriend['id'])
                    {
                        $friend = $this->doctrine
                                ->getRepository($this->userClassName)
                                ->findOneById($myFriend['id']);
                        $foundedFriends[] = $friend;
                    }
                }
            }
        }
        return $foundedFriends;
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->messageClassName
        ));
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'message';
    }
}