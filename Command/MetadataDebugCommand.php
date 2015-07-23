<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Symfony\Component\Console\Helper\Table;

/**
 * Comand to show the current metadata mappings
 */
class MetadataDebugCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('massive:search:metadata:debug');
        $this->setDescription('List all off the classes which are known by the metadata factory');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = $this->getContainer()->get('massive_search.metadata.factory');

        $table = new Table($output);
        $table->setHeaders(array('Class', 'Status'));
        foreach ($factory->getAllClassNames() as $className) {
            $status = 'ok';
            try {
                $factory->getMetadataForClass($className);
            } catch (\Exception $e) {
                $status = '<error>failed</error>';
            }
            $table->addRow(array($className, $status));
        }
        $table->render();
    }
}
