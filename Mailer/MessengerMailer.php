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

use Fulgurio\SocialNetworkBundle\Entity\Message;
use FOS\UserBundle\Model\UserInterface;

class MessengerMailer extends AbstractMailer
{
    /**
     * Message
     *
     * @param UserInterface $user
     * @param Message $message
     */
    public function sendMessageEmailMessage(UserInterface $user, Message $message)
    {
        if (!$user->getSendMsgToEmail())
        {
            return;
        }
        $data = array(
            'user' => $user,
            'message' => $message
        );
        $subject = $this->templating->render(
                $this->parameters['message.subject'],
                $data
        );
        $bodyText = $this->templating->render(
                $this->parameters['message.template.text'],
                $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['message.template.html'],
                $data
        );
        $this->sendEmailMessage(
                $this->parameters['message.from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML,
                $this->parameters['message.from_name']
        );
    }

    /**
     * Answer of a message
     *
     * @param UserInterface $user
     * @param Message $message
     * @param Message $answer
     */
    public function sendAnswerEmailMessage(UserInterface $user, Message $message, Message $answer)
    {
        if (!$user->getSendMsgToEmail())
        {
            return;
        }
        $data = array(
            'user' => $user,
            'message' => $message,
            'answer' => $answer
        );
        $subject = $this->templating->render(
                $this->parameters['answer.subject'],
                $data
        );
        $bodyText = $this->templating->render(
                $this->parameters['answer.template.text'],
                $data
        );
        $bodyHTML = $this->templating->render(
                $this->parameters['answer.template.html'],
                $data
        );
        $this->sendEmailMessage(
                $this->parameters['answer.from'],
                $user->getEmail(),
                $subject,
                $bodyText,
                $bodyHTML,
                $this->parameters['answer.from_name']
        );
    }
}