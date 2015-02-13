<?php

/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Metadata\MetadataFactory;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Symfony\Component\Console\Output\OutputInterface;
use Metadata\ClassMetadata as SearchMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadata as OrmMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;

class DoctrineOrmIndexRebuildSubscriber implements EventSubscriberInterface
{
    /**
     * @var ClassMetadataFactory
     */
    private $objectManager;

    /**
     * @var MetadataFactory
     */
    private $searchMetadataFactory;

    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @var array
     */
    private $purged = array();

    /**
     * @param ObjectManager $objectManager
     * @param MetadataFactory $searchMetadataFactory
     * @param SearchManager $searchManager
     */
    public function __construct(
        ObjectManager $objectManager,
        MetadataFactory $searchMetadataFactory,
        SearchManager $searchManager
    )
    {
        $this->objectManager = $objectManager;
        $this->searchMetadataFactory = $searchMetadataFactory;
        $this->searchManager = $searchManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SearchEvents::INDEX_REBUILD => 'rebuildIndex'
        );
    }

    public function rebuildIndex(IndexRebuildEvent $event)
    {
        $output = $event->getOutput();
        $filter = $event->getFilter();
        $purge = $event->getPurge();

        $metadataFactory = $this->objectManager->getMetadataFactory();
        $metadata = $metadataFactory->getAllMetadata();

        foreach ($metadata as $class) {
            if ($filter && !preg_match('{' . $filter .'}', $class)) {
                continue;
            }

            $searchMeta = $this->searchMetadataFactory->getMetadataForClass($class->getName());

            if (null === $searchMeta) {
                continue;
            }

            $classMetadata = $searchMeta->getOutsideClassMetadata();

            if ($purge) {
                $this->doPurge($output, $classMetadata, $class);
            }

            $this->rebuildClass($output, $class);
        }
    }

    private function doPurge(OutputInterface $output, ClassMetadata $classMetadata, OrmMetadata $ormMetadata)
    {
        foreach ($classMetadata->getIndexMetadatas() as $indexMetadata) {
            $indexName = $indexMetadata->getIndexName();

            if (isset($this->purged[$indexName])) {
                return;
            }

            $output->writeln('Purging index [' . $indexName . ']');
            $this->searchManager->purge($indexName);
            $this->purged[$indexName] = true;
        }
    }

    private function rebuildClass(OutputInterface $output, OrmMetadata $ormMetadata)
    {
        $output->writeln('Rebuilding: ' . $ormMetadata->getName());
    }
}
