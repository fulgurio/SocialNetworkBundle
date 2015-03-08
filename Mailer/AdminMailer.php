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
 * Admin mailer
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class AdminMailer extends AbstractMailer
{
    /**
     * Contact email sender
     *
     * @param UserInterface $user
     * @param string $subject
     * @param string $message
     */
    public function sendContactMessage(UserInterface $user, $subject, $message)
    {
        $data = array('user' => $user, 'subject' => $subject, 'content' => $message);
        $bodyText = $this->templating->render(
                $this->parameters['contact.textTemplate'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['contact.htmlTemplate'], $data
        );

        $this->sendEmailMessage(
                $this->parameters['contact.from'],
                $user->getEmail(),
                $subject,
                $bodyHTML,
                $bodyText
        );
    }
}