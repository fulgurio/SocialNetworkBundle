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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\CallbackValidator;

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
    public function buildForm(FormBuilder $builder, array $options)
    {
        $container = $this->container;
        $builder
            ->add('username', 'text', array(
                'required' => TRUE
            ))
            ->add('email', 'email', array(
                'required' => TRUE
            ))
            ->add('newPassword', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'fulgurio.socialnetwork.add.password.no_match',
                'required' => FALSE,
                'property_path' => FALSE
            ))
            ->add('avatarFile', 'file', array('required' => FALSE))
            ->addValidator(new CallbackValidator(function(FormInterface $form) use ($container) {
                $request = $container->get('request');
                $isUpdate = $request->get('userId') ? TRUE : FALSE;
                $usernameField = $form->get('username');
                if (trim($usernameField->getData()) == '')
                {
                    $usernameField->addError(new FormError('fulgurio.socialnetwork.add.username.not_blank'));
                }
                $emailField = $form->get('email');
                if (trim($emailField->getData()) == '')
                {
                    $emailField->addError(new FormError('fulgurio.socialnetwork.add.email.not_blank'));
                }
                $newPasswordField = $form->get('newPassword')->get('first');
                if (!$isUpdate && trim($newPasswordField->getData()) === '')
                {
                    $newPasswordField->addError(new FormError('fulgurio.socialnetwork.add.password.not_blank'));
                }
            })
        );
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