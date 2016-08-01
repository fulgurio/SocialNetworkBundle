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
use FOS\UserBundle\Form\Type\ResettingFormType as BaseType;

/**
 * Resetting form type
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class ResettingFormType extends BaseType
{
    /**
     * (non-PHPdoc)
     * @see FOS\UserBundle\Form\Type\ResettingFormType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('new', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'fulgurio.socialnetwork.lost_password.password_no_match'
        ));
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\AbstractType::getBlockPrefix()
     */
    public function getBlockPrefix()
    {
        return 'fulgurio_social_network_resetting_type';
    }
}
