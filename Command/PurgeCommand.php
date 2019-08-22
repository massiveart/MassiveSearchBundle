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

use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to purge search indexes.
 */
class PurgeCommand extends Command
{
    protected static $defaultName = 'massive:search:purge';

    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function __construct(
        SearchManagerInterface $searchManager,
        QuestionHelper $questionHelper = null
    ) {
        parent::__construct(self::$defaultName);

        $this->searchManager = $searchManager;
        $this->questionHelper = $questionHelper ?: new QuestionHelper();
    }

    public function configure()
    {
        $this->setDescription('Purge one, many or all indexes.');
        $this->setHelp(<<<'EOT'
Purge one, many or all indexes:

    $ %command.full_name%

Execute without any arguments in order to see the active indexes, use the
<comment>--index=<foo></comment> option in order to specify specific indexes or
<comment>--all</comment> to purge all indexes
EOT
        );
        $this->addOption('index', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify index to remove');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Purge ALL indexes.');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Do not ask for confirmation.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $indexes = $input->getOption('index');
        $all = $input->getOption('all');
        $force = $input->getOption('force');

        $allIndexes = $this->searchManager->getIndexNames();

        if (!$all && empty($indexes)) {
            return $this->listIndexes($output, $allIndexes);
        }

        if ($all) {
            $indexes = $this->searchManager->getIndexNames();
        }

        $unknownIndexes = array_diff($indexes, $allIndexes);

        if ($unknownIndexes) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown indexes "%s", known indexes: "%s"',
                implode('", "', $unknownIndexes),
                implode('", "', $allIndexes)
            ));
        }

        foreach ($indexes as $indexName) {
            $question = new ConfirmationQuestion(sprintf(
                'Are you sure you want to purge index "%s"? ', $indexName
            ), false);

            if (true === $force || $this->questionHelper->ask($input, $output, $question)) {
                $this->searchManager->purge($indexName);
            }
        }
    }

    private function listIndexes(OutputInterface $output, array $indexNames)
    {
        if (!$indexNames) {
            $output->writeln('No indexes currently exist.');

            return;
        }

        $output->writeln('<info>Specify the option: </>--index=<index_name><info> where </><index_name><info> is one of the following:</>');
        $output->write(PHP_EOL);

        foreach ($indexNames as $indexName) {
            $output->writeln('  ' . $indexName);
        }
    }
}
