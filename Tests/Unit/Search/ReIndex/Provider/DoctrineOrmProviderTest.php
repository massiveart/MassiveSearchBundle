<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Reindex\Provider;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata as SearchClassMetadata;
use Massive\Bundle\SearchBundle\Search\Reindex\Provider\DoctrineOrmProvider;
use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactory;

class DoctrineOrmProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MetadataFactory
     */
    private $searchMetadataFactory;

    /**
     * @var SearchClassMetadata
     */
    private $searchMetadata;

    /**
     * @var OrmClassMetadata
     */
    private $ormMetadata;

    /**
     * @var ClassMetadataFactory
     */
    private $ormMetadataFactory;

    /**
     * @var DoctrineOrmProvider
     */
    private $provider;

    /**
     * @var ClassHierarchyMetadata
     */
    private $hierarchyMetadata;

    /**
     * @var QueryBuilder
     */
    private $repository;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var AbstractQuery
     */
    private $query;

    public function setUp()
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->searchMetadataFactory = $this->prophesize(MetadataFactory::class);
        $this->ormMetadataFactory = $this->prophesize(ClassMetadataFactory::class);

        $this->provider = new DoctrineOrmProvider(
            $this->entityManager->reveal(),
            $this->searchMetadataFactory->reveal()
        );

        $this->ormMetadata = $this->prophesize(OrmClassMetadata::class);
        $this->hierarchyMetadata = $this->prophesize(ClassHierarchyMetadata::class);
        $this->searchMetadata = $this->prophesize(SearchClassMetadata::class);
        $this->repository = $this->prophesize(TestRepository::class);
        $this->queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->query = $this->prophesize(AbstractQuery::class);
    }

    /**
     * It should return all the class fqns.
     */
    public function testClassFqns()
    {
        $this->ormMetadata->name = 'stdClass';

        $this->entityManager->getMetadataFactory()->willReturn($this->ormMetadataFactory->reveal());
        $this->searchMetadataFactory->getMetadataForClass('stdClass')->willReturn($this->hierarchyMetadata->reveal());
        $this->hierarchyMetadata->getOutsideClassMetadata()->willReturn($this->searchMetadata->reveal());
        $this->ormMetadataFactory->getAllMetadata()->willReturn([
            $this->ormMetadata->reveal(),
        ]);

        $classFqns = $this->provider->getClassFqns();
        $this->assertEquals(['stdClass'], $classFqns);
    }

    /**
     * It should not return class FQNs NOT managed by the search manager.
     */
    public function testClassFqnsNotManaged()
    {
        $this->ormMetadata->name = 'stdClass';

        $this->entityManager->getMetadataFactory()->willReturn($this->ormMetadataFactory->reveal());
        $this->searchMetadataFactory->getMetadataForClass('stdClass')->willReturn(null);
        $this->hierarchyMetadata->getOutsideClassMetadata()->willReturn($this->searchMetadata->reveal());

        $this->ormMetadataFactory->getAllMetadata()->willReturn([
            $this->ormMetadata->reveal(),
        ]);

        $classFqns = $this->provider->getClassFqns();
        $this->assertEquals([], $classFqns);
    }

    /**
     * It should provide a slice of entities.
     */
    public function testProvideSlice()
    {
        $class = 'stdClass';
        $offset = 10;
        $max = 50;
        $entity = new \stdClass();

        $this->entityManager->getRepository($class)->willReturn($this->repository->reveal());
        $this->searchMetadataFactory->getMetadataForClass('stdClass')->willReturn($this->hierarchyMetadata->reveal());
        $this->hierarchyMetadata->getOutsideClassMetadata()->willReturn($this->searchMetadata->reveal());
        $this->repository->createQueryBuilder('d')->willReturn(
            $this->queryBuilder->reveal()
        );

        $this->queryBuilder->setFirstResult($offset)->shouldBeCalled();
        $this->queryBuilder->setMaxResults($max)->shouldBeCalled();
        $this->queryBuilder->getQuery()->willReturn($this->query->reveal());
        $this->query->execute()->willReturn([
            $entity,
        ]);

        $entities = $this->provider->provide($class, $offset, $max);

        $this->assertEquals([
            $entity,
        ], $entities);
    }

    /**
     * It should support the deprecated method of the repository returning a list of entities.
     */
    public function testProvideSliceDeprecated()
    {
        $class = 'stdClass';
        $entity = new \stdClass();
        $entities = array_fill(0, 20, $entity);

        $this->entityManager->getRepository($class)->willReturn($this->repository->reveal());
        $this->searchMetadataFactory->getMetadataForClass('stdClass')->willReturn($this->hierarchyMetadata->reveal());
        $this->hierarchyMetadata->getOutsideClassMetadata()->willReturn($this->searchMetadata->reveal());
        $this->repository->createQueryBuilder('d')->willReturn(
            $this->queryBuilder->reveal()
        );

        $this->searchMetadata->getReindexRepositoryMethod()->willReturn('deprecatedMethod');
        $this->repository->deprecatedMethod(
            $this->queryBuilder->reveal()
        )->willReturn($entities);

        $entities = $this->provider->provide($class, 0, 10);
        $this->assertCount(10, $entities);
        $entities = $this->provider->provide($class, 10, 10);
        $this->assertCount(10, $entities);
        $entities = $this->provider->provide($class, 20, 10);
        $this->assertCount(0, $entities);
    }
}

class TestRepository extends EntityRepository
{
    public function deprecatedMethod()
    {
    }
}
