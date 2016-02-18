<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\EventSubscriber;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Metadata\MetadataFactory;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

/**
 * Rebuilds the indexes which relate to Doctrine ORM query.
 */
class DoctrineOrmIndexRebuildSubscriber implements EventSubscriberInterface
{
    /**
     * @var ClassMetadataFactory
     */
    private $entityManager;

    /**
     * @var MetadataFactory
     */
    private $searchMetadataFactory;

    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @param EntityManager $entityManager
     * @param MetadataFactory $searchMetadataFactory
     * @param SearchManager $searchManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MetadataFactory $searchMetadataFactory,
        SearchManager $searchManager
    ) {
        $this->entityManager = $entityManager;
        $this->searchMetadataFactory = $searchMetadataFactory;
        $this->searchManager = $searchManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SearchEvents::INDEX_REBUILD => 'rebuildIndex',
        ];
    }

    /**
     * Rebuild the index.
     *
     * @param IndexRebuildEvent $event
     */
    public function rebuildIndex(IndexRebuildEvent $event)
    {
        $output = $event->getOutput();
        $filter = $event->getFilter();

        $metadataFactory = $this->entityManager->getMetadataFactory();
        $metadatas = $metadataFactory->getAllMetadata();

        foreach ($metadatas as $class) {
            if ($filter && !preg_match('{' . $filter . '}', $class->name)) {
                continue;
            }

            $searchMeta = $this->searchMetadataFactory->getMetadataForClass($class->name);

            if (null === $searchMeta) {
                continue;
            }

            $classMetadata = $searchMeta->getOutsideClassMetadata();

            $this->rebuildClass($output, $classMetadata);
        }
    }

    /**
     * Retrieve and rebuild the index for all the Entities for the given
     * metadata.
     *
     * @param OutputInterface $output
     * @param ClassMetadata $class
     */
    private function rebuildClass(OutputInterface $output, ClassMetadata $class)
    {
        $output->write('<comment>Rebuilding</comment>: ' . $class->name);

        $repositoryMethod = $class->getReindexRepositoryMethod();
        $repositoryMethod = $repositoryMethod ?: 'findAll';

        $repository = $this->entityManager->getRepository($class->name);

        if (!method_exists($repository, $repositoryMethod)) {
            throw new \InvalidArgumentException(sprintf(
                'Repository method "%s" does not exist.',
                $repositoryMethod
            ));
        }

        $query = $repository->$repositoryMethod();

        if (!$query instanceof Query) {
            @trigger_error('The repository method should reutrn a Doctrine\ORM\Query, not a collection of query.', E_USER_DEPRECATED);
            return $this->indexEntities($output, $query);
        }

        do {
            $entities = $query->execute();
            $this->indexEntities($output, $entities);
        $output->writeln(sprintf(
            $count
        ));
        } while ($entities);
    }

    private function indexEntities(OutputInterface $output, $entities)
    {
        if (!$entities) {
            return;
        }

        $count = 0;
        foreach ($entities as $entity) {
            $this->searchManager->index($entity);
            ++$count;
        }

        return $count;
    }
}
