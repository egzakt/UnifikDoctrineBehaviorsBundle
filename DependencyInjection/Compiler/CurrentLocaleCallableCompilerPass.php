<?php

namespace Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\DependencyInjection\Reference;

class CurrentLocaleCallableCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('knp.doctrine_behaviors.translatable_listener.current_locale_callable.class', 'Egzakt\\DoctrineBehaviorsBundle\\ORM\\Translatable\\CurrentLocaleCallable');
    }
}
