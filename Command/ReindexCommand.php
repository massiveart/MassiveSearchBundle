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

use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;
use Massive\Bundle\SearchBundle\Search\Reindex\LocalizedReindexProviderInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderRegistry;
use Massive\Bundle\SearchBundle\Search\Reindex\ResumeManagerInterface;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to build (or rebuild) the search index.
 */
class ReindexCommand extends Command
{
    protected static $defaultName = 'massive:search:reindex';

    /**
     * @var string
     */
    private $env;

    /**
     * @var ResumeManagerInterface
     */
    private $resumeManager;

    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var ReindexProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function __construct(
        ResumeManagerInterface $resumeManager,
        SearchManager $searchManager,
        ReindexProviderRegistry $providerRegistry,
        $env,
        QuestionHelper $questionHelper = null
    ) {
        parent::__construct(self::$defaultName);

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
        $this->setDescription('Rebuild search index');
        $this->setHelp(<<<'EOT'
This command will launch an event will trigger the search index to be rebuilt
on all "drivers" which support reindexing. Each driver will index all of
the entities/documents which it manages.
EOT
        );
        $this->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Batch size', 50);
        $this->addOption('provider', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Provider name');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $formatterHelper = new FormatterHelper();

        $startTime = microtime(true);

        if ('prod' !== $this->env) {
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
                    $this->resumeManager->removeCheckpoints($providerName);
                }
            }
        }

        $providerNames = $input->getOption('provider');

        if (empty($providerNames)) {
            $providers = $this->providerRegistry->getProviders();
        } else {
            $providers = [];
            foreach ($providerNames as $providerName) {
                $providers[$providerName] = $this->providerRegistry->getProvider($providerName);
            }
        }

        foreach ($providers as $providerName => $provider) {
            $output->writeln(sprintf('<info>provider "</info>%s<info>"</info>', $providerName));
            $output->write(PHP_EOL);

            foreach ($provider->getClassFqns() as $classFqn) {
                $return = $this->reindexClass($output, $provider, $providerName, $classFqn, $batchSize);

                if (false === $return) {
                    break;
                }
            }

            $this->resumeManager->removeCheckpoints($providerName);
        }

        $output->writeln(sprintf(
            '<info>Index rebuild completed (</info>%ss %sb</info><info>)</info>',
            number_format(microtime(true) - $startTime, 2),
            number_format(memory_get_usage())
        ));
    }

    private function reindexClass(
        OutputInterface $output,
        ReindexProviderInterface $provider,
        $providerName,
        $classFqn,
        $batchSize
    ) {
        $count = $provider->getCount($classFqn);
        $checkpoint = $this->resumeManager->getCheckpoint($providerName, $classFqn);
        $offset = $checkpoint ?: 0;

        // If the offset is the same as the count then the job was already completed.
        // If more objects are in the database than before, then we will just index a couple
        // of more documents than normal.
        if ($offset === $count) {
            return;
        }

        $realCount = $count - $offset;

        $output->writeln(sprintf(
            '<comment>-- reindexing "</comment>%s<comment>" instances of "</comment>%s<comment>"</comment>',
            $realCount,
            $classFqn
        ));
        $output->write(PHP_EOL);

        $progress = new ProgressBar($output);
        $progress->start($realCount);
        $progress->setFormat('debug');

        // index in batches
        while (true) {
            $objects = $provider->provide($classFqn, $offset, $batchSize);

            if (0 === count($objects)) {
                $provider->cleanUp($classFqn);
                $this->resumeManager->setCheckpoint($providerName, $classFqn, $count);
                $progress->finish();

                break;
            }

            foreach ($objects as $object) {
                $locales = [null];

                if ($provider instanceof LocalizedReindexProviderInterface) {
                    $locales = $provider->getLocalesForObject($object);
                }

                try {
                    foreach ($locales as $locale) {
                        if (null !== $locale) {
                            $object = $provider->translateObject($object, $locale);
                        }

                        $this->searchManager->index($object, $locale);
                    }
                    $progress->advance();
                } catch (MetadataNotFoundException $e) {
                    $output->write(' No search mapping for this object');

                    return false;
                }
            }

            $offset += $batchSize;
            $this->resumeManager->setCheckpoint($providerName, $classFqn, $offset);

            $provider->cleanUp($classFqn);
        }

        $output->write(PHP_EOL . PHP_EOL);
    }
}
