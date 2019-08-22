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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to execute a query on the configured search engine.
 */
class QueryCommand extends Command
{
    protected static $defaultName = 'massive:search:query';

    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    public function __construct(SearchManagerInterface $searchManager)
    {
        parent::__construct(self::$defaultName);

        $this->searchManager = $searchManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addArgument('query', InputArgument::REQUIRED, 'Search query');
        $this->addOption('index', 'I', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Index to search');
        $this->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Index to search');
        $this->setDescription('Search the using a given query');
        $this->setHelp(
            <<<'EOT'
The %command.name_full% command will search the configured repository and present
the results.

     %command.name_full% "This is a query string" --index=content
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getArgument('query');
        $indexes = $input->getOption('index');
        $locale = $input->getOption('locale');

        $start = microtime(true);
        $hits = $this->searchManager->createSearch($query)->indexes($indexes)->locale($locale)->execute();
        $timeElapsed = microtime(true) - $start;

        $table = new Table($output);
        $table->setHeaders(['Score', 'ID', 'Title', 'Description', 'Url', 'Image', 'Class']);
        foreach ($hits as $hit) {
            $document = $hit->getDocument();
            $table->addRow(
                [
                    $hit->getScore(),
                    $document->getId(),
                    $document->getTitle(),
                    $this->truncate($document->getDescription(), 50),
                    $document->getUrl(),
                    $document->getImageUrl(),
                    $document->getClass(),
                ]
            );
        }
        $table->render();
        $output->writeln(sprintf('%s result(s) in %fs', count($hits), $timeElapsed));
    }

    /**
     * Truncate the given string.
     *
     * See: https://github.com/symfony/symfony/issues/11977
     *
     * @param string $text Text to truncate
     * @param int $length Length
     * @param string $suffix Suffix to append
     *
     * @return string
     */
    private function truncate($text, $length, $suffix = '...')
    {
        $computedLength = $length - strlen($suffix);

        return strlen($text) > $computedLength ? substr($text, 0, $computedLength) . $suffix : $text;
    }
}
