<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Unit\Search;

use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactory;
use Prophecy\PhpUnit\ProphecyTestCase;
use Prophecy\Argument;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;

class SearchManagerTest extends ProphecyTestCase
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var IndexMetadataInterface
     */
    private $metadata;

    /**
     * @var ClassHierarchyMetadata
     */
    private $classHierachyMetadata;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @var Product
     */
    private $product;

    public function setUp()
    {
        $this->adapter = $this->prophesize('Massive\Bundle\SearchBundle\Search\AdapterInterface');
        $this->metadataFactory = $this->prophesize('Metadata\MetadataFactory');
        $this->metadata = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata');
        $this->classHierachyMetadata = $this->prophesize('Metadata\ClassHierarchyMetadata');
        $this->classHierachyMetadata->getOutsideClassMetadata()->willReturn($this->metadata);
        $this->eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->converter = $this->prophesize('Massive\Bundle\SearchBundle\Search\ObjectToDocumentConverter');
        $this->document = $this->prophesize('Massive\Bundle\SearchBundle\Search\Document');
        $this->factory = new Factory();

        $this->searchManager = new SearchManager(
            $this->factory,
            $this->adapter->reveal(),
            $this->metadataFactory->reveal(),
            $this->converter->reveal(),
            $this->eventDispatcher->reveal()
        );

        $this->product = new \Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIndexNonObject()
    {
        $this->searchManager->index('asd');
    }

    /**
     * @expectedException Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException
     * @expectedExceptionMessage There is no search mappin
     */
    public function testIndexNoMetadata()
    {
        $this->metadataFactory
            ->getMetadataForClass('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product')
            ->willReturn(null);

        $this->searchManager->index($this->product);
    }

    public function testIndex()
    {
        $this->metadataFactory
            ->getMetadataForClass('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product')
            ->willReturn($this->classHierachyMetadata);

        $this->metadata->getName()->willReturn('test');
        $this->metadata->getIdField()->willReturn('id');
        $this->metadata->getUrlField()->willReturn('url');
        $this->metadata->getTitleField()->willReturn('title');
        $this->metadata->getLocaleField()->willReturn(null);
        $this->metadata->getDescriptionField()->willReturn('body');
        $this->metadata->getImageUrlField()->willReturn(null);
        $this->metadata->getFieldMapping()->willReturn(array(
            'title' => array(
                'type' => 'string',
            ),
            'body' => array(
                'type' => 'string',
            )
        ));
        $this->metadata->getIndexName()->willReturn('product');
        $this->converter->objectToDocument($this->metadata, $this->product)->willReturn($this->document);
        $this->adapter->index(Argument::type('Massive\Bundle\SearchBundle\Search\Document'));

        $this->searchManager->index($this->product);
    }
}
