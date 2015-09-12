<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Form\Type\MessengerList;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Fulgurio\SocialNetworkBundle\Entity\User;
use Fulgurio\SocialNetworkBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class NewListFormType extends AbstractType
{
    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User $currentUser
     */
    private $currentUser;

    /**
     * @var Symfony\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $userClassName;

    /**
     * @var string
     */
    private $userFriendshipClassName;


    /**
     * Constructor
     *
     * @param User $currentUser
     * @param Registry $doctrine
     * @param string $userClassName
     * @param string $userFriendshipClassName
     * @param string $messageTargetClassName
     */
    public function __construct(User $currentUser, Registry $doctrine, $userClassName, $userFriendshipClassName)
    {
        $this->currentUser = $currentUser;
        $this->doctrine = $doctrine;
        $this->userClassName = $userClassName;
        $this->userFriendshipClassName = $userFriendshipClassName;
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
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.add.name.is_required'))
                )
            ))
        ;
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
            $messageGroup = $form->getData();
            foreach ($friends as $friend)
            {
                $messageGroup->addUser($friend);
            }
            return;
        }
        $usernameTarget->addError(new FormError('fulgurio.socialnetwork.add.search.no_friend_found'));
    }

    /**
     * Get friends from username typed value
     *
     * @param array $usersId
     * @return array
     */
    private function getOnlyFriends($usersId)
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
            'data_class' => 'Fulgurio\SocialNetworkBundle\Entity\UserGroup'
        ));
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'group';
    }
}