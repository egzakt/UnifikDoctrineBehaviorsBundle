<?php

namespace Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

use Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener;

class EgzaktDoctrineBehaviorsCompilerPass implements CompilerPassInterface
{
    /**
     * Process
     *
     * Add some Compiler Pass
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
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

                        // Check if the service
//                        if (!$service instanceof SluggableListener) {
//                            throw new \Exception('The service class « ' . get_class($service) . ' » must extend the Egzakt\DoctrineBehaviorsBundle\ORM\Sluggable\SluggableListener class.');
//                        }

                        // Check the ClassAnalyzer dependency before injecting it
                        if (!$container->hasDefinition('knp.doctrine_behaviors.reflection.class_analyzer')) {
                            throw new \Exception('The service « knp.doctrine_behaviors.reflection.class_analyzer » is not defined. Did you forget to KnpLabs/DoctrineBehaviors to your project?');
                        }

                        // Inject it
                        $service->addMethodCall(
                            'setClassAnalyzer',
                            array(new Reference('knp.doctrine_behaviors.reflection.class_analyzer'))
                        );

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
