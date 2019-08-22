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

use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initializes the search adapter.
 */
class InitCommand extends Command
{
    protected static $defaultName = 'massive:search:init';

    /**
     * @var AdapterInterface
     */
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct(self::$defaultName);

        $this->adapter = $adapter;
    }

    public function configure()
    {
        $this->setDescription('Initializes the search bundle');
        $this->setHelp('This command will simply call the initialize method of the currently active search adapter.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->adapter->initialize();
    }
}
