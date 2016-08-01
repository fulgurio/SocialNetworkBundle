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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class AnswerMessageFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $messageClassName;


    /**
     * Constructor
     *
     * @param User $securityContext
     * @param Registry $doctrine
     * @param string $messageClassName
     */
    public function __construct($messageClassName)
    {
        $this->messageClassName = $messageClassName;
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\FormTypeInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('content', 'text', array(
                    'constraints' => array(
                        new NotBlank(array('message' => 'fulgurio.socialnetwork.new_message.content.not_blank'))
                    )
                ))
                ->add('file', 'file', array('required' => FALSE))
                ->add('submit', 'submit')
        ;
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => $this->messageClassName
        ));
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form\AbstractType::getBlockPrefix()
     */
    public function getBlockPrefix()
    {
        return 'answer';
    }
}
