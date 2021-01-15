<?php

namespace ICS\SocialNetworkBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SocialNetworkExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        $container->setParameter('medias', $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['NavigationBundle'])) {
            $loader->load('navigation.yaml');
        }

        if (isset($bundles['LiipImagineBundle'])) {
            $loader->load('liip_imagine.yaml');
        }
    }
}
