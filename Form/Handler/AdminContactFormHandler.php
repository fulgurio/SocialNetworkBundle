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

use Fulgurio\SocialNetworkBundle\Mailer\ContactMailer;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class AdminContactFormHandler
{
    /**
     * @var Fulgurio\SocialNetworkBundle\Mailer\ContactMailer
     */
    private $mailer;

    /**
     *
     * @var Symfony\Component\Form\Form
     */
    private $form;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;


    /**
     * Constructor
     *
     * @param Fulgurio\SocialNetworkBundle\Mailer\ContactMailer $mailer
     * @param Symfony\Component\Form\Form $form
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(ContactMailer $mailer, Form $form, Request $request)
    {
        $this->mailer = $mailer;
        $this->form = $form;
        $this->request = $request;
    }

    /**
     * Processing form values
     *
     * @param $user
     * @return boolean
     */
    public function process($user)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->bindRequest($this->request);
            if ($this->form->isValid())
            {
                $data = $this->form->getData();
                $this->mailer->sendAdminMessage(
                        $user,
                        $data['subject'],
                        $data['message']
                );
                return TRUE;
            }
        }
        return FALSE;
    }
}