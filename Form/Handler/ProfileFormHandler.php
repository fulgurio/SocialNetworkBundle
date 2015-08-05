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

use FOS\UserBundle\Form\Handler\ProfileFormHandler as BaseHandler;
use FOS\UserBundle\Model\UserInterface;

class ProfileFormHandler extends BaseHandler
{
    protected function onSuccess(UserInterface $user)
    {
        $this->userManager->updateUser($user);
    }
}