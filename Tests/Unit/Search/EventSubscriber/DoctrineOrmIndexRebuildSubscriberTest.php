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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Massive\Bundle\SearchBundle\Search\EventSubscriber\DoctrineOrmSubscriber;
use Doctrine\Common\Persistence\Mapping\ClassMetadata as OrmMetadata;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Metadata\MetadataFactory;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Metadata\ClassHierarchyMetadata;
use Symfony\Component\Console\Output\BufferedOutput;
use Massive\Bundle\SearchBundle\Search\EventSubscriber\DoctrineOrmIndexRebuildSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Prophecy\Argument;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;

class DoctrineOrmIndexRebuildSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var ObjectManager
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
        $this->objectManager = $this->prophesize(ObjectManager::class);
        $this->ormMetadataFactory = $this->prophesize(ClassMetadataFactory::class);
        $this->ormMetadata = $this->prophesize(OrmMetadata::class);
        $this->searchMetadataFactory = $this->prophesize(MetadataFactory::class);
        $this->searchHierarchy = $this->prophesize(ClassHierarchyMetadata::class);
        $this->searchMetadata = $this->prophesize(ClassMetadata::class);
        $this->searchManager = $this->prophesize(SearchManager::class);

        $this->subscriber = new DoctrineOrmIndexRebuildSubscriber(
            $this->objectManager->reveal(),
            $this->searchMetadataFactory->reveal(),
            $this->searchManager->reveal()
        );

    }

    /**
     * It should call the named repository method if requested
     */
    public function testRepositoryMethod()
    {
        $this->prepareReindex();

        $this->searchMetadata->getReindexRepositoryMethod()->willReturn('foobar');
        $this->searchManager->index(Argument::type('stdClass'))->shouldBeCalledTimes(1);

        $event = $this->createEvent(null, false);
        $this->subscriber->rebuildIndex($event);
    }

    /**
     * It should throw an exception if the repository method does not exist
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
        $this->ormMetadataFactory->getAllMetadata()->willReturn(array(
            $this->ormMetadata->reveal()
        ));
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
        return array(new \stdClass);
    }
}
