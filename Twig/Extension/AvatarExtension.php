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


/**
 * User avatar function for Twig.
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class AvatarExtension extends \Twig_Extension
{
    /**
     * @var Twig_Environment
     */
    protected $environment;


    /**
     * (non-PHPdoc)
     * @see Twig_Extension::initRuntime()
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Init Twig functions
     */
    public function getFunctions()
    {
        return array(
            'avatar' =>    new \Twig_Function_Method($this, 'getAvatar', array('is_safe' => array('html'))),
        );
    }

    /**
     * Return user avatar
     *
     * @param User|array $user
     */
    public function getAvatar($user)
    {
        if (is_array($user))
        {
            return '/uploads/' . $user['id'] . '/' . $user['avatar'];
        }
        if ($user->getAvatar() != '')
        {
            return $user->displayAvatar();
        }
        return $this->environment->getExtension('assets')->getAssetUrl('bundles/fulguriosocialnetwork/images/avatar.png');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'avatar';
    }
}
