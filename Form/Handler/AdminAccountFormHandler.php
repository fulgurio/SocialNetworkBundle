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

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class AdminAccountFormHandler
{
    /**
     * @var type
     */
    private $userManager;

    /**
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
     * @param FOS\UserBundle\Model\UserManagerInterface $userManager
     * @param Symfony\Component\Form\Form $form
     * @param Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(UserManagerInterface $userManager, Form $form, Request $request)
    {
        $this->userManager = $userManager;
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
                $newPassword = $this->form->get('newPassword')->getData();
                if (trim($newPassword))
                {
                    $user->setPlainPassword($newPassword);
                    $this->userManager->updatePassword($user);
                }
                $this->userManager->updateUser($user);
                return TRUE;
            }
        }
        return FALSE;
    }
}