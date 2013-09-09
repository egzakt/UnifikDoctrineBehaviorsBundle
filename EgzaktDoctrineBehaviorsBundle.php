<?php

namespace Egzakt\DoctrineBehaviorsBundle;

use Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler\CurrentLocaleCallableCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EgzaktDoctrineBehaviorsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CurrentLocaleCallableCompilerPass());
    }
}
