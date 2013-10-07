<?php

namespace Flexy\DoctrineBehaviorsBundle;

use Flexy\DoctrineBehaviorsBundle\DependencyInjection\Compiler\FlexyDoctrineBehaviorsCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FlexyDoctrineBehaviorsBundle extends Bundle
{
    /**
     * Build
     *
     * Add some Compiler Pass to bridge the KnpLabs Doctrine Behaviors with the Flexy Standard Distribution
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FlexyDoctrineBehaviorsCompilerPass());
    }
}
