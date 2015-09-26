<?php

namespace Unifik\DoctrineBehaviorsBundle\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Unifik\DoctrineBehaviorsBundle\Model\Tree\MaterializedPath;

/**
 * Class GenerateMaterializedPathCommand
 * @package Unifik\DoctrineBehaviorsBundle\Command
 */
class GenerateMaterializedPathCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('unifik:materialized-path:generate')
            ->setDescription('Reset and generate path for tree entity with MaterializedPath strategy.')
            ->addArgument('name', InputArgument::REQUIRED, 'Target entity. ie: BundleNameSpace:Entity')
            ->setHelp(<<<EOT
The <info>unifik:materialized-path:generate</info> command reset
materialized_path field and generate new node Id:

You have to limit reset of tree entities:

* To a single entity:

  <info>php app/console unifik:materialized-path:generate MyCustomBundle:Section</info>
  <info>php app/console unifik:materialized-path:generate MyCustomBundle/Entity/Section</info>

* NOTE: <info>Entity must use MaterializedPath strategy</info>.

EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('unifik_system.core')->setCurrentAppName('backend');

        $doctrine = $this->getContainer()->get('doctrine');
        $name = strtr($input->getArgument('name'), '/', '\\');

        if (false !== $pos = strpos($name, ':')) {
            $name = $doctrine->getAliasNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
        }

        if (!class_exists($name)) {
            $output->writeln(['', sprintf('Entity "<error>%s</error>" does not exist.', $name), '']);
            exit;
        }

        $reflClass = $doctrine->getManager()->getClassMetadata($name)->getReflectionClass();

        if (!in_array('Unifik\DoctrineBehaviorsBundle\Model\Tree\MaterializedPath', $reflClass->getTraitNames())) {
            $output->writeln(['', sprintf('Entity "<error>%s</error>" must use "<error>Unifik\\DoctrineBehaviorsBundle\\Model\\Tree\\MaterializedPath</error>" trait.', $name), '']);
            exit;
        }

        $entities = $doctrine->getRepository($name)->findBy(['parent' => null]);

        foreach ($entities as $entity) {
            /** @var MaterializedPath $entity */
            $entity->resetNodeId();
        }

        $doctrine->getManager()->flush();

        $output->writeln(['', sprintf('MaterializedPath has been successfully generated for all <info>%s</info> entities!', $name), '']);
    }
}