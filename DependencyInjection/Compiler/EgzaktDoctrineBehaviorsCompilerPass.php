<?php

namespace Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\DependencyInjection\Reference;

class EgzaktDoctrineBehaviorsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // CurrentLocaleCallable Listener
        $container->setParameter('knp.doctrine_behaviors.translatable_listener.current_locale_callable.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Translatable\\CurrentLocaleCallable');

        // Sluggable Listener
        $container->setParameter('knp.doctrine_behaviors.sluggable_listener.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Sluggable\\SluggableListener');

        // Translatable Listener
        $container->setParameter('knp.doctrine_behaviors.translatable_listener.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Translatable\\TranslatableListener');
    }
}
