<?php

namespace Egzakt\DoctrineBehaviorsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EgzaktDoctrineBehaviorsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('egzakt_doctrine_behaviors.uploadable.upload_root_dir', $config['uploadable']['upload_root_dir']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../../../../knplabs/doctrine-behaviors/config'));
        $loader->load('orm-services.yml');
    }
}
