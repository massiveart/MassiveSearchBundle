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
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Search\ReIndex\ReIndexProviderRegistry;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Comand to build (or rebuild) the search index.
 */
class ReindexCommand extends Command
{
    private $env;
    private $resumeManager;
    private $searchManager;
    private $providerRegistry;
    private $questionHelper;

    public function __construct(
        ResumeManagerInterface $resumeManager,
        SearchManager $searchManager,
        ReIndexProviderRegistry $providerRegistry,
        $env,
        QuestionHelper $questionHelper = null
    ) {
        parent::__construct();
        $this->resumeManager = $resumeManager;
        $this->searchManager = $searchManager;
        $this->providerRegistry = $providerRegistry;
        $this->env = $env;
        $this->questionHelper = $questionHelper ?: new QuestionHelper();
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
        //$this->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter classes which will be indexed (regex)');
        //$this->addOption('purge', null, InputOption::VALUE_NONE, 'Purge the index before reindexing');
        $this->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Batch size', 50);

    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $formatterHelper = new FormatterHelper();

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

        //$purge = $input->getOption('purge');
        //$filter = $input->getOption('filter');
        $batchSize = $input->getOption('batch-size');

        $providerNames = $this->resumeManager->getUnfinishedProviders();

        if (count($providerNames) > 0) {
            foreach ($providerNames as $providerName) {
                $question = new ConfirmationQuestion(sprintf(
                    '<question>Provider "%s" did not finish. Do you wish to resume?</question> ',
                    $providerName
                ));
                $response = $this->questionHelper->ask($input, $output, $question, true);

                if (false === $response) {
                    $this->resumeManager->removeCheckpoint($providerName);
                }
            }
        }

        foreach ($this->providerRegistry->getProviders() as $providerName => $provider) {
            $output->writeln(sprintf('provider "%s"', $providerName));
            $output->write(PHP_EOL);

            foreach ($provider->getClassFqns() as $classFqn) {
                $count = $provider->getCount($classFqn);
                $checkpoint = $this->resumeManager->getCheckpoint($providerName, $classFqn);
                $offset = $checkpoint ?: 0;

                // If the offset is the same as the count then the job was already completed.
                // If more objects are in the database than before, then we will just index a couple
                // of more documents than normal.
                if ($offset === $count - 1) {
                    continue;
                }

                $realCount = $count - $offset;

                $output->writeln(sprintf(
                    '-- reindexing "%s" instances of "%s"', 
                    $realCount, 
                    $classFqn
                ));

                $progress = new ProgressBar($output);
                $progress->start($realCount);

                // index in batches
                while ($objects = $provider->provide($classFqn, $offset, $batchSize)) {
                    $this->resumeManager->setCheckpoint($providerName, $classFqn, $offset);

                    foreach ($objects as $object) {
                        $progress->advance();
                        $output->write(' Mem: ' . number_format(memory_get_usage()) . 'b');
                        $this->searchManager->index($object);
                    }

                    $offset += $batchSize;
                }

                $output->write(PHP_EOL . PHP_EOL);
            }

            $this->resumeManager->removeCheckpoints($providerName);
        }

        $output->write(PHP_EOL);
        $output->writeln(sprintf(
            'Index Rebuild Completed (%ss %sb)',
            number_format(microtime(true) - $startTime, 2),
            number_format(memory_get_usage())
        ));
    }
}
