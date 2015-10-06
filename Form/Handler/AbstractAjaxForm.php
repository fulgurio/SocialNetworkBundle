<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fulgurio\SocialNetworkBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractAjaxForm
{
    /**
     * @var Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var boolean
     */
    protected $hasErrors = FALSE;

    /**
     * @var array
     */
    private $errors;


    /**
     * Constructor
     *
     * @param Form $form
     * @param Request $request
     * @param TranslatorInterface $translator
     */
    public function __construct(Form $form, Request $request, TranslatorInterface $translator)
    {
        $this->form = $form;
        $this->request = $request;
        $this->translator = $translator;
    }

    /**
     * Init errors array before return it
     *
     * @return array
     */
    public function getErrors()
    {
        if (!is_array($this->errors))
        {
            $this->errors = array();
            $this->updateErrors($this->form);
        }
        return $this->errors;
    }

    /**
     * Set errors
     *
     * @param Symfony\Component\Form\Form $elt
     * @param string $prefix
     */
    private function updateErrors(Form $elt, $prefix = '')
    {
        $eltName = $prefix . $elt->getName();
        foreach ($elt->getErrors() as $error)
        {
            if (!isset($this->errors[$eltName]))
            {
                $this->errors[$eltName] = array();
            }
            $this->errors[$eltName][] = $this->translate($error->getMessage());
        }
        foreach ($elt->all() as $child)
        {
            $this->updateErrors($child, $eltName . '_');
        }
    }

    /**
     * Translate message
     *
     * @param string $message
     * @return string
     */
    protected function translate($message)
    {
        return $this->translator->trans($message);
    }

    /**
     * Warn if form has an error
     *
     * @return boolean
     */
    public function hasError()
    {
        return $this->hasErrors;
    }
}