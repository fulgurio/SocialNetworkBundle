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

use Symfony\Component\Form\FormBuilder;
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
    protected function buildUserForm(FormBuilder $builder, array $options)
    {
        parent::buildUserForm($builder, $options);
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'fulgurio.socialnetwork.profile.edit_profil.password_no_match'
        ));
	}
}