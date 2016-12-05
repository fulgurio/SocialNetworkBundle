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

use Fulgurio\SocialNetworkBundle\Entity\User;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * User avatar function for Twig.
 *
 * @author Vincent Guerard <v.guerard@fulgurio.net>
 */
class AvatarExtension extends \Twig_Extension
{
    /**
     * @var UploaderHelper
     */
    private $vichUploadHelper;

    /**
     * @var string
     */
    private $defaultAvatar;


    /**
     * Constructor
     *
     * @param UploaderHelper $vichUploadHelper
     */
    function __construct(UploaderHelper $vichUploadHelper, $defaultAvatar)
    {
        $this->vichUploadHelper = $vichUploadHelper;
        $this->defaultAvatar = $defaultAvatar;
    }

    /**
     * Init Twig functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('avatar', array($this, 'getAvatar'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Return user avatar
     *
     * @param \Twig_Environment $env
     * @param User|array $user
     * @return string
     */
    public function getAvatar(\Twig_Environment $env, $user)
    {
        if (is_array($user))
        {
            return $this->vichUploadHelper->asset(
                    $user,
                    'avatarFile',
                    'Fulgurio\SocialNetworkBundle\Entity\User'
            );
        }
        elseif ($user->getAvatar() != '')
        {
            return $this->vichUploadHelper->asset($user, 'avatarFile');
        }
        return $env->getExtension('assets')
                ->getAssetUrl($this->defaultAvatar);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'avatar';
    }
}
