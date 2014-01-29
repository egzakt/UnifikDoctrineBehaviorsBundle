<?php

namespace Unifik\DoctrineBehaviorsBundle;

use Unifik\DoctrineBehaviorsBundle\DependencyInjection\Compiler\UnifikDoctrineBehaviorsCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UnifikDoctrineBehaviorsBundle extends Bundle
{
    /**
     * Build
     *
     * Add some Compiler Pass to bridge the KnpLabs Doctrine Behaviors with the Unifik Standard Distribution
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new UnifikDoctrineBehaviorsCompilerPass());
    }
}
