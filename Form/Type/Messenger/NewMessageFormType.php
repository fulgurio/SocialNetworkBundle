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

use Fulgurio\SocialNetworkBundle\Entity\MessageTarget;
use Fulgurio\SocialNetworkBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Bundle\DoctrineBundle\Registry;


class NewMessageFormType extends AbstractType
{
    /**
     * @var Fulgurio\SocialNetworkBundle\Entity\User $currentUSer
     */
    private $currentUser;

    /**
     * @var Symfony\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;


    /**
     * Constructor
     *
     * @param User $currentUser
     * @param Registry $doctrine
     */
    public function __construct(User $currentUser, Registry $doctrine)
    {
        $this->currentUser = $currentUser;
        $this->doctrine = $doctrine;
    }

    /**
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username_target', 'text', array(
                'required' => FALSE,
                'property_path' => FALSE,
            ))
            ->add('id_targets', 'choice', array(
                'multiple' => TRUE,
                'property_path' => FALSE
            ))
            ->add('subject', 'text')
            ->add('content', 'text')
            ->addValidator(new CallbackValidator(array($this, 'checkTarget')))
            ;
    }

    /**
     * Check targets value
     *
     * @param FormInterface $form
     */
    public function checkTarget(FormInterface $form)
    {
        $userRepository = $this->doctrine
                ->getRepository('FulgurioSocialNetworkBundle:User');
        $idTargets = $form->get('id_targets');
        $usersId = (count($idTargets->getData()) > 0) ? $idTargets->getData() : array();
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
                $target = new MessageTarget();
                $target->setMessage($message);
                $target->setTarget($friend);
                $this->doctrine->getEntityManager()->persist($target);
            }
            $message->addMessageTarget($target);
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
    private function getOnlyFriends($usersId)
    {
        $myFriends = $this->doctrine
                ->getRepository('FulgurioSocialNetworkBundle:UserFriendship')
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
                                ->getRepository('FulgurioSocialNetworkBundle:User')
                                ->findOneById($myFriend['id']);
                        $foundedFriends[] = $friend;
                    }
                }
            }
        }
        return $foundedFriends;
    }

    /**
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'message';
    }
}