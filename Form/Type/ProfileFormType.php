<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

/**
 * Profile form type
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class ProfileFormType extends BaseType
{
    /**
     * (non-PHPdoc)
     * @see FOS\UserBundle\Form\Type\ProfileFormType::buildUserForm()
     */
    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildUserForm($builder, $options);
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'fulgurio.socialnetwork.profile.edit_profil.password_no_match'
        ))
        ->add('avatarFile', 'file', array('required' => FALSE))
        ->add('send_msg_to_email', 'checkbox', array('required' => FALSE));
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\AbstractType::getBlockPrefix()
     */
    public function getBlockPrefix()
    {
        return 'fulgurio_social_network_profile_type';
    }
}