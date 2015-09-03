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
     * @var Twig_Environment
     */
    protected $environment;

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
        return $this->environment
                ->getExtension('assets')
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
