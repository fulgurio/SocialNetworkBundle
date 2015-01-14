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
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

/**
 * Registration form type
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class RegistrationFormType extends BaseType
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\FormTypeInterface::buildForm()
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email', 'email')
            ->add('plainPassword', 'password');
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'registration';
    }
}