<?php

namespace Massive\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\TableHelper;

class MassiveSearchQueryCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('massive:search:query');
        $this->addArgument('query', InputArgument::REQUIRED, 'Search query');
        $this->addOption('index', 'I', InputOption::VALUE_REQUIRED, 'Index to search');
        $this->setDescription('Search the using a given query');
        $this->setHelp(<<<EOT
The %command.name_full% command will search the configured repository and present
the results.

     %command.name_full% "This is a query string" --index=content
EOT
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getArgument('query');
        $index = $input->getOption('index');

        $searchManager = $this->getContainer()->get('massive_search.search_manager');
        $res = $searchManager->search($query, $index);

        $table = new TableHelper();
        $table->setHeaders(array('Score', 'ID', 'Title', 'Description', 'Url', 'Class'));
        foreach ($res as $hit) {
            $document = $hit->getDocument();
            $table->addRow(array($hit->getScore(), $document->getId(), $document->getTitle(), $document->getDescription(), $document->getUrl(), $document->getClass()));
        }
        $table->render($output);
    }
}
