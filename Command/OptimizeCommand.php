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
use Massive\Bundle\SearchBundle\Search\OptimizeableAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    protected static $defaultName = 'massive:search:optimize';

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
        $this->setDescription('Optimize all search indices. Currently only relevant when using the `zend_lucene` adapter.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->adapter instanceof OptimizeableAdapterInterface) {
            $output->writeln(\sprintf('Adapter "%s" does not support index optimization.', \get_class($this->adapter)));

            return 0;
        }

        $output->writeln('Optimize indexes:');
        $output->writeln('');

        foreach ($this->adapter->listIndexes() as $indexName) {
            $output->writeln(' - ' . $indexName);

            $this->adapter->optimize($indexName);
        }

        $output->writeln('');

        $output->writeln('<info>All indeces optimized!</info>');

        return 0;
    }
}
