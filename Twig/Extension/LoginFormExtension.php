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

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

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
     * Constructor
     *
     * @param Symfony\Component\HttpFoundation\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Init Twig functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('last_username',   array($this, 'getLastUsername'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Return last username for login form
     *
     * @return string
     */
    public function getLastUsername()
    {
        return (null === $this->session) ? '' : $this->session->get(Security::LAST_USERNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'login_form';
    }
}
