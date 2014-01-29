<?php

namespace Unifik\DoctrineBehaviorsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('unifik_doctrine_behaviors');

        $rootNode
            ->children()
                ->arrayNode('uploadable')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('upload_root_dir')->defaultValue('../web/uploads')->end()
                        ->scalarNode('upload_web_dir')->defaultValue('/uploads')->end()
                    ->end()
                ->end()

                ->arrayNode('blameable')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_entity')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
