<?php

namespace Unifik\DoctrineBehaviorsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UnifikDoctrineBehaviorsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('unifik_doctrine_behaviors.uploadable.upload_root_dir', $config['uploadable']['upload_root_dir']);
        $container->setParameter('unifik_doctrine_behaviors.uploadable.upload_web_dir', $config['uploadable']['upload_web_dir']);
        $container->setParameter('unifik_doctrine_behaviors.blameable.listener.user_entity', $config['blameable']['user_entity']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
