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
                $this->parameters['invit.subjectTemplate'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['invit.textTemplate'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['invit.htmlTemplate'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML
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
                $this->parameters['accept.subjectTemplate'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['accept.textTemplate'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['accept.htmlTemplate'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML
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
                $this->parameters['remove.subjectTemplate'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['remove.textTemplate'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['remove.htmlTemplate'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML
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
                $this->parameters['refuse.subjectTemplate'],
                array('user' => $user)
        );
        $bodyText = $this->templating->render(
                $this->parameters['refuse.textTemplate'],
                array('user' => $user)
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['refuse.htmlTemplate'],
                array('user' => $user)
        );
        $this->sendEmailMessage(
                $this->parameters['from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML
        );
    }
}