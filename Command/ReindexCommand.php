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

use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\ReIndex\ResumeManagerInterface;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Comand to build (or rebuild) the search index.
 */
class ReindexCommand extends Command
{
    private $env;
    private $eventDispatcher;
    private $resumeManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ResumeManagerInterface $resumeManager,
        $env
    ) {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
        $this->resumeManager = $resumeManager;
        $this->env = $env;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('massive:search:reindex');
        $this->setDescription('Rebuild search index');
        $this->setHelp(<<<'EOT'
This command will launch an event will trigger the search index to be rebuilt
on all "drivers" which support reindexing. Each driver will index all of
the entities/documents which it manages.
EOT
        );
        $this->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter classes which will be indexed (regex)');
        $this->addOption('purge', null, InputOption::VALUE_NONE, 'Purge the index before reindexing');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $formatterHelper = new FormatterHelper();
        $questionHelper = new QuestionHelper();

        $startTime = microtime(true);

        if ($this->env !== 'prod') {
            $output->writeln(
                $formatterHelper->formatBlock(
                    sprintf(
                        'WARNING: You are running this command in the `%s` environment - this may increase memory usage. Running in `prod` environment is generally better.',
                        $this->env
                    ),
                    'comment',
                    true
                )
            );
        }

        $purge = $input->getOption('purge');
        $filter = $input->getOption('filter');

        $checkpoints = $this->resumeManager->getCheckpoints();

        if (count($checkpoints) > 0) {
            foreach ($checkpoints as $name => $value) {
                $question = new ConfirmationQuestion(sprintf(
                    '<question>Checkpoint found for "%s", do you wish to resume from %d?</question> ',
                    $name, $value
                ));
                $response = $questionHelper->ask($input, $output, $question, true);

                if (false === $response) {
                    $this->resumeManager->removeCheckpoint($name);
                }
            }
        }

        $event = new IndexRebuildEvent($filter, $purge, $output);
        $this->eventDispatcher->dispatch(SearchEvents::INDEX_REBUILD, $event);

        $output->write(PHP_EOL);
        $output->writeln(sprintf(
            'Index Rebuild Completed (%ss %sb)',
            number_format(microtime(true) - $startTime, 2),
            number_format(memory_get_usage())
        ));
    }
}
