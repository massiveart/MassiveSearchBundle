<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * This command returns some vendor specific information about
 * the currently configured search implementation
 */
class StatusCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('massive:search:status');
        $this->setDescription('Return the status of the configured search engine');
        $this->setHelp(<<<EOT
Return detailed information about the current search implementation
EOT
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $searchManager = $this->getContainer()->get('massive_search.search_manager');
        $status = $searchManager->getStatus();

        $table = new Table($output);
        $table->setHeaders(array('Field', 'Value'));

        foreach ($status as $field => $value) {
            $table->addRow(array($field, $value));
        }

        $table->render();
    }
}
