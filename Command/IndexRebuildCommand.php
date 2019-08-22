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

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated use ReindexCommand
 */
class IndexRebuildCommand extends ReindexCommand
{
    protected static $defaultName = 'massive:search:index:rebuild';

    public function configure()
    {
        parent::configure();

        $this->setName(self::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $formatterHelper = new FormatterHelper();
        $output->writeln(
            $formatterHelper->formatBlock(sprintf(
                'DEPRECATED: The `%s` command is deprecated, use `massive:search:reindex` instead.',
                $this->getName()
            ), 'comment', true)
        );

        return parent::execute($input, $output);
    }
}
