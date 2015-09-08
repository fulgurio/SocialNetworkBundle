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
use FOS\UserBundle\Mailer\MailerInterface;

/**
 * Fos overrider mailer
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class FosMailer extends AbstractMailer implements MailerInterface
{
    /**
     * Welcome message email
     *
     * @param UserInterface $user
     */
    public function sendRegistrationEmailMessage(UserInterface $user)
    {
        $subject = $this->templating->render(
                $this->parameters['registration.subject'],
                array('user' => $user));
        $bodyText = $this->templating->render(
                $this->parameters['registration.template.text'],
                array('user' => $user));
        $bodyHTML = $this->templating->render(
                $this->parameters['registration.template.html'],
                array('user' => $user));
        $this->sendEmailMessage(
                $this->parameters['registration.from_mail'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML
                );
    }

    /**
     * Confirmation email sender
     *
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $url = $this->router->generate('fos_user_registration_confirm',
                array('token' => $user->getConfirmationToken()), true);
        $data = array(
            'user' => $user,
            'confirmationUrl' => $url);
        $subject = $this->templating->render(
                $this->parameters['confirmation.subject'], $data
        );
        $bodyText = $this->templating->render(
                $this->parameters['confirmation.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['confirmation.template.html'], $data
        );
        $this->sendEmailMessage(
                $this->parameters['confirmation.from_mail'],
                $user->getEmail(),
                $subject,
                $bodyHTML,
                $bodyText
        );
    }

    /**
     * Resetting email sender
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $url = $this->router->generate('fos_user_resetting_reset',
                array('token' => $user->getConfirmationToken()), true);
        $data = array(
            'user' => $user,
            'confirmationUrl' => $url);
        $subject = $this->templating->render(
                $this->parameters['resetting.subject'], $data
        );
        $bodyText = $this->templating->render(
                $this->parameters['resetting.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['resetting.template.html'], $data
        );
        $this->sendEmailMessage(
                $this->parameters['resetting.from_mail'],
                $user->getEmail(),
                $subject,
                $bodyHTML,
                $bodyText
        );
    }
}