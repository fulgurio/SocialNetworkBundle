<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FulgurioSocialNetworkExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->addEmailsConfig($container, $config['admin_email']['contact'], 'admin_email_contact');
        $this->addEmailsConfig($container, $config['admin_email']['remove_avatar'], 'admin_email_remove_avatar');
        $this->addFriendshipConfig($container, $config['friendship']);
    }

    /**
     * Adding email data config
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @param string $parameterName
     */
    private function addEmailsConfig(ContainerBuilder $container, array $config, $parameterName)
    {
        if (isset($config['from']))
        {
            $container->setParameter('fulgurio_social_network.' . $parameterName . '.from', $config['from']);
        }
        if (isset($config['subject']))
        {
            $container->setParameter('fulgurio_social_network.' . $parameterName . '.subject', $config['subject']);
        }
        $container->setParameter('fulgurio_social_network.' . $parameterName . '.text', $config['text']);
        $container->setParameter('fulgurio_social_network.' . $parameterName . '.html', $config['html']);
    }

    private function addFriendshipConfig(ContainerBuilder $container, array $config)
    {
        $container->setParameter('fulgurio_social_network.friendship_email_from', $config['email']['from']);
        $container->setParameter('fulgurio_social_network.friendship_nb_refusals', $config['nb_refusals']);
        $this->addEmailsConfig($container, $config['email']['invit'],  'friendship_email_invit');
        $this->addEmailsConfig($container, $config['email']['accept'], 'friendship_email_accept');
        $this->addEmailsConfig($container, $config['email']['refuse'], 'friendship_email_refuse');
        $this->addEmailsConfig($container, $config['email']['remove'], 'friendship_email_remove');
    }
}
