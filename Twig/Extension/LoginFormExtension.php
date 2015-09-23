<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Twig\Extension;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;

/**
 * LogoutUrlHelper provides generator functions for the logout URL to Twig.
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class LoginFormExtension extends \Twig_Extension
{
    /**
     * @var Symfony\Component\HttpFoundation\Session
     */
    private $session;

    /**
     * @var Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider
     */
    private $csrfProvider;

    /**
     * Constructor
     *
     * @param Symfony\Component\HttpFoundation\Session $session
     * @param Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider $csrfProvider
     */
    public function __construct(Session $session, SessionCsrfProvider $csrfProvider)
    {
        $this->session = $session;
        $this->csrfProvider = $csrfProvider;
    }

    /**
     * Init Twig functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('last_username',   array($this, 'getLastUsername'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('csrf_token',      array($this, 'getCsrfLoginToken'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Return last username for login form
     *
     * @return string
     */
    public function getLastUsername()
    {
        return (null === $this->session) ? '' : $this->session->get(SecurityContext::LAST_USERNAME);
    }

    /**
     * Return csrf_token for login form
     *
     * @return string
     */
    public function getCsrfLoginToken()
    {
        return $this->csrfProvider->generateCsrfToken('authenticate');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'login_form';
    }
}
