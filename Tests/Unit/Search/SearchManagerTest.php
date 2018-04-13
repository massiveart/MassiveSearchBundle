<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Unit\Search;

use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Decorator\IndexNameDecoratorInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Value;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface;
use Massive\Bundle\SearchBundle\Search\ObjectToDocumentConverter;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Product;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SearchManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @var IndexMetadata
     */
    private $indexMetadata;

    /**
     * @var ObjectToDocumentConverter
     */
    private $converter;

    /**
     * @var Document
     */
    private $document;

    /**
     * @var IndexNameDecoratorInterface
     */
    private $indexNameDecorator;

    /**
     * @var Product
     */
    private $product;

    public function setUp()
    {
        $this->adapter = $this->prophesize(AdapterInterface::class);
        $this->provider = $this->prophesize(ProviderInterface::class);
        $this->indexMetadata = $this->prophesize(IndexMetadata::class);
        $this->metadata = $this->prophesize(ClassMetadata::class);
        $this->metadata->getIndexMetadatas()->willReturn([
            $this->indexMetadata->reveal(),
        ]);

        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->converter = $this->prophesize(ObjectToDocumentConverter::class);
        $this->document = $this->prophesize(Document::class);
        $this->fieldEvaluator = $this->prophesize(FieldEvaluator::class);
        $this->indexNameDecorator = $this->prophesize(IndexNameDecoratorInterface::class);

        $this->searchManager = new SearchManager(
            $this->adapter->reveal(),
            $this->provider->reveal(),
            $this->converter->reveal(),
            $this->eventDispatcher->reveal(),
            $this->indexNameDecorator->reveal(),
            $this->fieldEvaluator->reveal()
        );

        $this->product = new Product();
    }

    /**
     * It should throw an exception if a non-object is passed to be indexed.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testIndexNonObject()
    {
        $this->searchManager->index('asd');
    }

    /**
     * @expectedException \Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException
     * @expectedExceptionMessage There is no search mapping
     */
    public function testIndexNoMetadata()
    {
        $this->provider
            ->getMetadataForObject($this->product)
            ->willReturn(null);

        $this->searchManager->index($this->product);
    }

    public function testIndex()
    {
        $this->provider
            ->getMetadataForObject($this->product)
            ->willReturn($this->metadata->reveal());

        $this->indexMetadata->getName()->willReturn('test');
        $this->indexMetadata->getIdField()->willReturn('id');
        $this->indexMetadata->getUrlField()->willReturn('url');
        $this->indexMetadata->getTitleField()->willReturn('title');
        $this->indexMetadata->getLocaleField()->willReturn(null);
        $this->indexMetadata->getDescriptionField()->willReturn('body');
        $this->indexMetadata->getImageUrlField()->willReturn(null);
        $this->indexMetadata->getFieldMapping()->willReturn([
            'title' => [
                'type' => 'string',
            ],
            'body' => [
                'type' => 'string',
            ],
        ]);
        $this->indexMetadata->getIndexName()->willReturn(new Value('product'));
        $this->converter->objectToDocument($this->indexMetadata, $this->product)->willReturn($this->document);
        $this->converter->getFieldEvaluator()->willReturn($this->fieldEvaluator->reveal());
        $this->adapter->index(Argument::type('Massive\Bundle\SearchBundle\Search\Document'));

        $this->searchManager->index($this->product);
    }
}
