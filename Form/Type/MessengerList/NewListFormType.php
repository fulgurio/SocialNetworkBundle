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
        $builder
            ->add('username_target', 'text', array(
                'required' => FALSE,
                'mapped' => FALSE,
            ))
            ->add('id_targets', 'entity', array(
                'multiple' => TRUE,
                'mapped' => FALSE,
                'class' => $this->userClassName,
                'choices' => array()
            ))
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'addUsers'))
            ->addEventListener(FormEvents::POST_SUBMIT, array($this, 'checkTarget'))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.add.name.is_required'))
                )
            ))
        ;
    }

    /**
     * Ajouter les utilisateurs saisie
     * On les charge après car ils sont chargé via Ajax, et avec les tests de
     * données de Symfony, les valeurs doivent être présentes avant le test
     *
     * @param FormEvent $event
     */
    public function addUsers(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        if (isset($data['id_targets']))
        {
            $this->targetUsers = $this->doctrine
                    ->getRepository($this->userClassName)
                    ->findBy(array('id' => $data['id_targets']));
            $form
                ->add('id_targets', 'entity', array(
                    'multiple' => TRUE,
                    'mapped' => FALSE,
                    'class' => $this->userClassName,
                    'choices' => $this->targetUsers
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
            $messageGroup = $form->getData();
            $allowed = FALSE;
            foreach ($usersId as $userId)
            {
                $allowedUser = $userRepository->find($userId);
                if ($userRepository->allowContactThemself($this->currentUser, $allowedUser))
                {
                    $allowed = TRUE;
                    $messageGroup->addUser($allowedUser);
                }
            }
            if ($allowed)
            {
                return;
            }
        }
        $usernameTarget->addError(new FormError('fulgurio.socialnetwork.add.search.no_friend_found'));
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