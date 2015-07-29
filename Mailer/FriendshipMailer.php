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
 * Friendship mailer
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class FriendshipMailer extends AbstractMailer
{
    /**
     * Invit invitation message
     *
     * @param UserInterface $user
     */
    public function sendInvitMessage(UserInterface $user)
    {
        $subject = $this->templating->render(
                $this->parameters['invit.subject'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['invit.template.txt'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['invit.template.html'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML,
                $this->parameters['from_name']
        );
    }

    /**
     * Accept invitation message
     *
     * @param UserInterface $user
     */
    public function sendAcceptMessage(UserInterface $user)
    {
        $subject = $this->templating->render(
                $this->parameters['accept.subject'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['accept.template.txt'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['accept.template.html'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML,
                $this->parameters['from_name']
        );
    }


    /**
     * Remove invitation message
     *
     * @param UserInterface $user
     */
    public function sendRemoveInvitMessage(UserInterface $user)
    {
        $subject = $this->templating->render(
                $this->parameters['remove.subject'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['remove.template.txt'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['remove.template.html'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML,
                $this->parameters['from_name']
            );
    }

    /**
     * Refusal invitation message
     *
     * @param UserInterface $user
     */
    public function sendRefusalMessage(UserInterface $user)
    {
        $subject = $this->templating->render(
                $this->parameters['refuse.subject'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['refuse.template.txt'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['refuse.template.html'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML,
                $this->parameters['from_name']
        );
    }
}