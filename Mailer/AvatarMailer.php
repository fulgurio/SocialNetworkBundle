<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;

/**
 * Avatar mailer
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class AvatarMailer extends AbstractMailer
{
    /**
     * Send email on avatar deletion
     *
     * @param UserInterface $user
     */
    public function sendAdminMessage(UserInterface $user)
    {
        $subject = $this->templating->render(
                $this->parameters['admin.subject']
        );
        $data = array('user' => $user, 'subject' => $subject);
        $bodyText = $this->templating->render(
                $this->parameters['admin.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['admin.template.html'], $data
        );
        $this->sendEmailMessage(
                $this->parameters['admin.from'],
                $user->getEmail(),
                $subject,
                $bodyHTML,
                $bodyText,
                $this->parameters['admin.from_name']
        );
    }
}