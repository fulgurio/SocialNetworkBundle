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

use Fulgurio\SocialNetworkBundle\Entity\User;

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
     * @param User $user
     */
    public function sendInvitMessage(User $user)
    {
        $subject = $this->templating->render(
                $this->parameters['invit.subject'],
                array('user' => $user)
        );
        $data = array('user' => $user, 'subject' => $subject);
        $bodyText = $this->templating->render(
                $this->parameters['invit.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['invit.template.html'], $data
        );
        if ($user->getSendMsgToEmail())
        {
            $this->sendEmailMessage(
                    $this->parameters['from'],
                    $user->getEmail(),
                    $subject,
                    $bodyText,
                    $bodyHTML
            );
        }
        $this->messenger->sendMessage($user, $subject, $bodyHTML, TRUE);
    }

    /**
     * Accept invitation message
     *
     * @param User $user
     */
    public function sendAcceptMessage(User $user)
    {
        $subject = $this->templating->render(
                $this->parameters['accept.subject'],
                array('user' => $user)
        );
        $data = array('user' => $user, 'subject' => $subject);
        $bodyText = $this->templating->render(
                $this->parameters['accept.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['accept.template.html'], $data
        );
        if ($user->getSendMsgToEmail())
        {
            $this->sendEmailMessage(
                    $this->parameters['from'],
                    $user->getEmail(),
                    $subject,
                    $bodyText,
                    $bodyHTML
            );
        }
        $this->messenger->sendMessage($user, $subject, $bodyHTML, TRUE);
    }


    /**
     * Remove invitation message
     *
     * @param User $user
     */
    public function sendRemoveInvitMessage(User $user)
    {
        $subject = $this->templating->render(
                $this->parameters['remove.subject'],
                array('user' => $user)
        );
        $data = array('user' => $user, 'subject' => $subject);
        $bodyText = $this->templating->render(
                $this->parameters['remove.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['remove.template.html'], $data
        );
        if ($user->getSendMsgToEmail())
        {
            $this->sendEmailMessage(
                    $this->parameters['from'],
                    $user->getEmail(),
                    $subject,
                    $bodyText,
                    $bodyHTML
                );
        }
        $this->messenger->sendMessage($user, $subject, $bodyHTML, TRUE);
    }

    /**
     * Refusal invitation message
     *
     * @param User $user
     */
    public function sendRefusalMessage(User $user)
    {
        $subject = $this->templating->render(
                $this->parameters['refuse.subject'],
                array('user' => $user)
        );
        $data = array('user' => $user, 'subject' => $subject);
        $bodyText = $this->templating->render(
                $this->parameters['refuse.template.text'], $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['refuse.template.html'], $data
        );
        if ($user->getSendMsgToEmail())
        {
            $this->sendEmailMessage(
                    $this->parameters['from'],
                    $user->getEmail(),
                    $subject,
                    $bodyText,
                    $bodyHTML
            );
        }
        $this->messenger->sendMessage($user, $subject, $bodyHTML, TRUE);
    }
}