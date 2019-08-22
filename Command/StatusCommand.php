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

use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command returns some vendor specific information about
 * the currently configured search implementation.
 */
class StatusCommand extends Command
{
    protected static $defaultName = 'massive:search:status';

    /**
     * @var SearchManager
     */
    private $searchManager;

    public function __construct(SearchManagerInterface $searchManager)
    {
        parent::__construct(self::$defaultName);

        $this->searchManager = $searchManager;
    }

    public function configure()
    {
        $this->setDescription('Return the status of the configured search engine');
        $this->setHelp(<<<'EOT'
Return detailed information about the current search implementation
EOT
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $status = $this->searchManager->getStatus();

        $table = new Table($output);
        $table->setHeaders(['Field', 'Value']);

        foreach ($status as $field => $value) {
            $table->addRow([$field, $value]);
        }

        $table->render();
    }
}
