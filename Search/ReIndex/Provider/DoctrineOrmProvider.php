<?php

namespace Massive\Bundle\SearchBundle\Search\ReIndex\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Metadata\MetadataFactory;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Search\ReIndex\ReIndexProviderInterface;

class DoctrineOrmProvider implements ReIndexProviderInterface
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
     * @param EntityManager $entityManager
     * @param MetadataFactory $searchMetadataFactory
     * @param SearchManager $searchManager
     */
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
        $classFqns = array();

        foreach ($metadataFactory->getAllMetadata() as $classMetadata) {
            if (null === $this->searchMetadataFactory->getMetadataForClass($classMetadata->name)) {
                continue;
            }
            $classFqns[] = $classMetadata->name;
        }

        return $classFqns;
    }

    /**
     * {@inheritdoc}
     */
    public function provide($classFqn, $offset, $maxResults)
    {
        $repository = $this->entityManager->getRepository($classFqn);
        $queryBuilder = $repository->createQueryBuilder('d')
            ->setFirstResult($offset)
            ->setMaxResults($maxResults);

        return $queryBuilder->getQuery()->execute();
    }

    public function getCount($classFqn)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('count(a.id)');
        $queryBuilder->from($classFqn, 'a');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
