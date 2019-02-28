<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Reindex\Provider;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderInterface;
use Metadata\MetadataFactory;

/**
 * Provides Doctrine ORM entities for reindexing.
 */
class DoctrineOrmProvider implements ReindexProviderInterface
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
     * @deprecated BC hack. @see this::provide()
     *
     * @var object[]
     */
    private $cachedEntities = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        MetadataFactory $searchMetadataFactory
    ) {
        $this->entityManager = $entityManager;
        $this->searchMetadataFactory = $searchMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassFqns()
    {
        $metadataFactory = $this->entityManager->getMetadataFactory();
        $classFqns = [];

        foreach ($metadataFactory->getAllMetadata() as $classMetadata) {
            if (null === $this->searchMetadataFactory->getMetadataForClass($classMetadata->name)) {
                continue;
            }
            $classFqns[] = $classMetadata->name;
        }

        return $classFqns;
    }

    /**
     * BC Note: Previous versions of the MassiveSearchBundle expected a collection of entities to
     *          be returned from the repository via. a custom repository method. The expected behavior
     *          now is that a query builder will be passed and NOTHING should be returned.
     *
     * {@inheritdoc}
     */
    public function provide($classFqn, $offset, $maxResults)
    {
        if (!empty($this->cachedEntities)) {
            return $this->sliceEntities($offset, $maxResults);
        }

        $repository = $this->entityManager->getRepository($classFqn);
        $metadata = $this->searchMetadataFactory->getMetadataForClass($classFqn);

        $repositoryMethod = $metadata->getOutsideClassMetadata()->getReindexRepositoryMethod();

        $queryBuilder = $repository->createQueryBuilder('d');

        if ($repositoryMethod) {
            $result = $repository->$repositoryMethod($queryBuilder);

            if (is_array($result)) {
                @trigger_error('Reindex repository methods should NOT return anything. Use the passed query builder instead.');
                $this->cachedEntities = $result;

                return $this->sliceEntities($offset, $maxResults);
            }
        }

        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($maxResults);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cleanUp($classFqn)
    {
        if (count($this->cachedEntities) > 0) {
            return;
        }

        $this->entityManager->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($classFqn)
    {
        $repository = $this->entityManager->getRepository($classFqn);
        $metadata = $this->searchMetadataFactory->getMetadataForClass($classFqn);
        $repositoryMethod = $metadata->getOutsideClassMetadata()->getReindexRepositoryMethod();
        $queryBuilder = $repository->createQueryBuilder('d');

        if ($repositoryMethod) {
            $result = $repository->$repositoryMethod($queryBuilder);

            if ($result) {
                @trigger_error(
                    'Reindex repository methods should NOT return anything. Use the passed query builder instead.'
                );

                $queryBuilder = $this->entityManager->createQueryBuilder()
                    ->from($classFqn, 'd');
            }
        }

        $queryBuilder->select('count(d.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function sliceEntities($offset, $maxResults)
    {
        $entities = array_slice($this->cachedEntities, $offset, $maxResults);

        if (count($entities) < $maxResults) {
            $this->cachedEntities = [];
        }

        return $entities;
    }
}
