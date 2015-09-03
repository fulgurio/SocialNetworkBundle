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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewListFormType extends AbstractType
{
    /**
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('idTargets', 'choice', array('multiple' => TRUE))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.add.name.is_required'))
                )
            ))
            ->add('search', 'text', array(
                'required' => FALSE,
                'mapped' => FALSE)
            )
            ->add('idUsers', 'choice', array(
                'multiple' => TRUE,
                'mapped' => FALSE)
            )
        ;
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