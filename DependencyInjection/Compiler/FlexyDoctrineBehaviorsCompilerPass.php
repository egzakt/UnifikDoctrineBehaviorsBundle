<?php

namespace Flexy\DoctrineBehaviorsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class FlexyDoctrineBehaviorsCompilerPass implements CompilerPassInterface
{
    /**
     * Inject dependencies in the Sluggable service
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // Get the list of Doctrine Event Subscriber, and check for two properties : type and entity
        $taggedServices = $container->findTaggedServiceIds(
            'doctrine.event_subscriber'
        );

        $sluggableService = $container->getDefinition('flexy_doctrine_behaviors.sluggable.listener');

        foreach ($taggedServices as $id => $tagAttributes) {

            // Loop through the services
            foreach ($tagAttributes as $attributes) {

                // It's the kind of service we're looking for
                if (array_key_exists('type', $attributes) && array_key_exists('entity', $attributes)) {

                    // If the type is sluggable
                    if ('sluggable' == $attributes['type']) {

                        $service = $container->getDefinition($id);

                        $service->addMethodCall(
                            'setEntityName',
                            array($attributes['entity'])
                        );

                        // Add this entity to the default Sluggable service excluded entities
                        // because this entity has a custom service
                        $sluggableService->addMethodCall(
                            'addExcludedEntity',
                            array($attributes['entity'])
                        );
                    }

                }
            }
        }
    }
}
