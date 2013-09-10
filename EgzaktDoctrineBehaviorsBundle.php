<?php

namespace Egzakt\DoctrineBehaviorsBundle;

use Egzakt\DoctrineBehaviorsBundle\DependencyInjection\Compiler\EgzaktDoctrineBehaviorsCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EgzaktDoctrineBehaviorsBundle extends Bundle
{
    /**
     * Build
     *
     * Add some Compiler Pass to bridge the KnpLabs Doctrine Behaviors with the Egzakt Standard Distribution
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EgzaktDoctrineBehaviorsCompilerPass());
    }
}
