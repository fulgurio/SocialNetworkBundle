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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminAccountFormType extends AbstractType
{
    private $container;

    /**
     * Construtor
     *
     * @param object $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'required' => TRUE,
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.add.username.not_blank'))
                )
            ))
            ->add('email', 'email', array(
                'required' => TRUE,
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.add.email.not_blank'))
                )
            ))
            ->add('avatarFile', 'file', array('required' => FALSE));

        $request = $this->container->get('request');
        if ($request->get('userId'))
        {
            $builder->add('newPassword', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'fulgurio.socialnetwork.add.password.not_blank',
                'required' => FALSE,
                'mapped' => FALSE
            ));
        }
        else
        {
            $builder->add('newPassword', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'fulgurio.socialnetwork.add.password.no_match',
                'required' => TRUE,
                'mapped' => FALSE,
                'constraints' => array(
                    new NotBlank(array('message' => 'fulgurio.socialnetwork.add.password.not_blank'))
                )
            ));
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'user';
    }
}