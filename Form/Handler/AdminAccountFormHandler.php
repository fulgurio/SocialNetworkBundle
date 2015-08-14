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
     * Constructor
     *
     * @param FOS\UserBundle\Model\UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Processing form values
     *
     * @param Symfony\Component\Form\Form $form
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    public function process(Form $form, Request $request)
    {
        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);
            if ($form->isValid())
            {
                $user = $form->getData();
                $newPassword = $form->get('newPassword')->getData();
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