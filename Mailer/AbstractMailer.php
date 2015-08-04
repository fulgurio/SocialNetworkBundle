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

use Fulgurio\SocialNetworkBundle\Service\Messenger;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Admin contact form type
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
abstract class AbstractMailer
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var Fulgurio\SocialNetworkBundle\Service\Messenger
     */
    protected $messenger;


    /**
     * Constructor
     *
     * @param Swift_Mailer $mailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param array $parameters
     */
    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * Send html and text email
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $bodyHTML
     * @param string $bodyText
     * @param string $fromName
     */
    protected function sendEmailMessage($from, $to, $subject, $bodyHTML, $bodyText, $fromName = NULL)
    {
        if (is_array($from))
        {
            $fromName = current($from);
            $from = key($from);
        }
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from, $fromName)
            ->setTo($to)
            ->setBody($bodyHTML, 'text/html')
            ->addPart($bodyText, 'text/plain');
        $this->mailer->send($message);
    }

    /**
     * $messenger setter
     * @param Messenger $messenger
     */
    public function setMessenger(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }
}