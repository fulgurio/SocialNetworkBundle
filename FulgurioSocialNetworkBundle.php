<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FulgurioSocialNetworkBundle extends Bundle
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\HttpKernel\Bundle\Bundle::getParent()
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
