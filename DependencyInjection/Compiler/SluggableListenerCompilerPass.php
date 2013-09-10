<?php

namespace Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\DependencyInjection\Reference;

class SluggableListenerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('knp.doctrine_behaviors.sluggable_listener.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Sluggable\\SluggableListener');
    }
}
