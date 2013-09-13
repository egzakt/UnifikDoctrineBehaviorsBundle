<?php

namespace Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\DependencyInjection\Reference;

class EgzaktDoctrineBehaviorsCompilerPass implements CompilerPassInterface
{
    /**
     * Process
     *
     * Add some Compiler Pass
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // CurrentLocaleCallable Listener
        $container->setParameter('knp.doctrine_behaviors.translatable_listener.current_locale_callable.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Translatable\\CurrentLocaleCallable');

        // Translatable Listener
        $container->setParameter('knp.doctrine_behaviors.translatable_listener.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Translatable\\TranslatableListener');

        // Get the list of Doctrine Event Subscriber, and check for two properties : type and entity
        $taggedServices = $container->findTaggedServiceIds(
            'doctrine.event_subscriber'
        );

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
                    }

                }
            }
        }
    }
}
