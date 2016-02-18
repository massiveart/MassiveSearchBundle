<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\EventSubscriber;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as OrmMetadata;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\EventSubscriber\DoctrineOrmIndexRebuildSubscriber;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactory;
use Prophecy\Argument;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineOrmIndexRebuildSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    /**
     * @var OrmMetadata
     */
    private $ormMetadataFactory;

    /**
     * @var OrmMetadata
     */
    private $ormMetadata;

    /**
     * @var MetadataFactory
     */
    private $searchMetadataFactory;

    /**
     * @var ClassHierarchyMetadata
     */
    private $searchHierarchy;

    /**
     * @var ClassMetadata
     */
    private $searchMetadata;

    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @var DoctrineOrmIndexRebuildSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->objectManager = $this->prophesize('Doctrine\ORM\EntityManagerInterface');
        $this->ormMetadataFactory = $this->prophesize('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $this->ormMetadata = $this->prophesize('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->searchMetadataFactory = $this->prophesize('Metadata\MetadataFactory');
        $this->searchHierarchy = $this->prophesize('Metadata\ClassHierarchyMetadata');
        $this->searchMetadata = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata');
        $this->searchManager = $this->prophesize('Massive\Bundle\SearchBundle\Search\SearchManager');

        $this->subscriber = new DoctrineOrmIndexRebuildSubscriber(
            $this->objectManager->reveal(),
            $this->searchMetadataFactory->reveal(),
            $this->searchManager->reveal()
        );
    }

    /**
     * It should call the named repository method if requested.
     */
    public function testRepositoryMethod()
    {
        $this->prepareReindex();

        $this->searchMetadata->getReindexRepositoryMethod()->willReturn('foobar');
        $this->searchManager->index(Argument::type('stdClass'))->shouldBeCalledTimes(1);

        $event = $this->createEvent(null, false);
        $this->subscriber->rebuildIndex($event);

        $this->assertContains('1 entities indexed', $this->output->fetch());
    }

    /**
     * It should throw an exception if the repository method does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Repository method "barfoo" does not exist
     */
    public function testRepositoryMethodNotExists()
    {
        $this->prepareReindex();

        $this->searchMetadata->getReindexRepositoryMethod()->willReturn('barfoo');

        $event = $this->createEvent(null, false);
        $this->subscriber->rebuildIndex($event);
    }

    private function createEvent($filter, $purge)
    {
        return new IndexRebuildEvent(
            $filter,
            $purge,
            $this->output
        );
    }

    private function prepareReindex()
    {
        $this->objectManager->getMetadataFactory()->willReturn($this->ormMetadataFactory);
        $this->searchHierarchy->getOutsideClassMetadata()->willReturn($this->searchMetadata->reveal());
        $this->ormMetadataFactory->getAllMetadata()->willReturn([
            $this->ormMetadata->reveal(),
        ]);
        $this->ormMetadata->name = 'stdClass';
        $this->searchMetadata->name = 'stdClass';
        $this->searchMetadataFactory->getMetadataForClass('stdClass')->willReturn($this->searchHierarchy->reveal());

        $this->objectManager->getRepository('stdClass')->willReturn(new TestRepository());
    }
}

class TestRepository
{
    public function foobar()
    {
        return [new \stdClass()];
    }
}
