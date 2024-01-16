<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Unit\Search\Adapter;

use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Filesystem\Filesystem;

class ZendLuceneAdapterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    private $dataPath;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Document
     */
    private $document;

    /**
     * @var Field
     */
    private $field1;

    /**
     * @var Field
     */
    private $field2;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function setUp()
    {
        $this->factory = $this->prophesize(Factory::class);
        $this->document = $this->prophesize(Document::class);
        $this->field1 = $this->prophesize(Field::class);
        $this->field2 = $this->prophesize(Field::class);
        $this->filesystem = $this->prophesize(Filesystem::class);
        $filesystem = new Filesystem();
        $this->dataPath = __DIR__ . '/../../../Resources/app/data';
        if (\file_exists($this->dataPath)) {
            $filesystem->remove($this->dataPath);
        }

        $this->document->getUrl()->willReturn('http://foobar.com');
        $this->document->getTitle()->willReturn('hallo');
        $this->document->getDescription()->willReturn('Hallo Goodbye');
        $this->document->getLocale()->willReturn('de');
        $this->document->getClass()->willReturn('Class');
        $this->document->getImageUrl()->willReturn('hallo.png');
    }

    /**
     * Listing indexes when the path does not exist should return an empty array.
     */
    public function testListIndexesNotExist()
    {
        $adapter = $this->createAdapter('/path-not-exist');
        $result = $adapter->listIndexes();
        $this->assertEquals([], $result);
    }

    /**
     * It should use the correct zend type based on the store and index attributes
     * of the field.
     *
     * @dataProvider provideIndexWithFieldType
     */
    public function testIndexWithFieldType($store, $index, $exception)
    {
        if ($exception) {
            list($exceptionType, $exceptionMessage) = $exception;
            $this->expectException($exceptionType, $exceptionMessage);
        }

        $adapter = $this->createAdapter($this->dataPath);
        $this->document->getId()->willReturn(12);
        $this->document->getFields()->willReturn(
            [
                $this->field1,
            ]
        );
        $this->document->getIndex()->willReturn('foo');

        $this->field1->getName()->willReturn('hallo');
        $this->field1->getValue()->willReturn('goodbye');
        $this->field1->getType()->willReturn(Field::TYPE_STRING);
        $this->field1->isStored()->willReturn($store);
        $this->field1->isIndexed()->willReturn($index);
        $this->field1->isAggregate()->willReturn(true);

        $luceneDocument = $adapter->index($this->document->reveal(), 'foo');

        $luceneField = $luceneDocument->getField('hallo');
        $this->assertEquals($store, $luceneField->isStored);
        $this->assertEquals($index, $luceneField->isIndexed);
    }

    public function provideIndexWithFieldType()
    {
        return [
            [
                true,
                true,
                null,
            ],
            [
                false,
                true,
                null,
            ],
            [
                true,
                false,
                null,
            ],
            [
                false,
                false,
                ['\InvalidArgumentException', 'cannot be both not indexed and not stored'],
            ],
        ];
    }

    /**
     * If the field is aggregate, its value should be aggregated into the aggregate field.
     */
    public function testIndexWithAggregate()
    {
        $adapter = $this->createAdapter($this->dataPath);
        $this->document->getId()->willReturn(12);
        $this->document->getIndex()->willReturn('foo');
        $this->document->getFields()->willReturn(
            [
                $this->field1,
                $this->field2,
            ]
        );

        foreach (['field1', 'field2'] as $fieldName) {
            $this->$fieldName->getName()->willReturn('hallo');
            $this->$fieldName->getValue()->willReturn('goodbye');
            $this->$fieldName->getType()->willReturn(Field::TYPE_STRING);
            $this->$fieldName->isStored()->willReturn(true);
            $this->$fieldName->isIndexed()->willReturn(true);
            $this->$fieldName->isAggregate()->willReturn(true);
        }

        $luceneDocument = $adapter->index($this->document->reveal(), 'foo');
        $aggregateField = $luceneDocument->getField(ZendLuceneAdapter::AGGREGATED_INDEXED_CONTENT);

        $this->assertEquals(
            'goodbye goodbye',
            $aggregateField->value,
            'It should aggregate the two field values'
        );
    }

    public function testSorting()
    {
        $document1 = $this->prophesize(Document::class);
        $document1->getId()->willReturn('1');
        $document1->getUrl()->willReturn('http://foobar.com');
        $document1->getTitle()->willReturn('Test');
        $document1->getDescription()->willReturn('Test in description field test.');
        $document1->getLocale()->willReturn('de');
        $document1->getClass()->willReturn('Class');
        $document1->getImageUrl()->willReturn('hallo.png');
        $document1->getIndex()->willReturn('foo_sorting');
        $field = $this->prophesize(Field::class);
        $field->getName()->willReturn('title');
        $field->getValue()->willReturn('Test');
        $field->getType()->willReturn(Field::TYPE_STRING);
        $field->isStored()->willReturn(true);
        $field->isIndexed()->willReturn(true);
        $field->isAggregate()->willReturn(true);
        $field2 = $this->prophesize(Field::class);
        $field2->getName()->willReturn('description');
        $field2->getValue()->willReturn('Test in description field test.');
        $field2->getType()->willReturn(Field::TYPE_STRING);
        $field2->isStored()->willReturn(true);
        $field2->isIndexed()->willReturn(true);
        $field2->isAggregate()->willReturn(true);
        $document1->getFields()->willReturn([
            $field,
            $field2,
        ]);

        $document2 = $this->prophesize(Document::class);
        $document2->getId()->willReturn('2');
        $document2->getUrl()->willReturn('http://foobar.com');
        $document2->getTitle()->willReturn('Other');
        $document2->getDescription()->willReturn('Test ony once.');
        $document2->getLocale()->willReturn('de');
        $document2->getClass()->willReturn('Class');
        $document2->getImageUrl()->willReturn('hallo.png');
        $document2->getIndex()->willReturn('foo_sorting_2');
        $field = $this->prophesize(Field::class);
        $field->getName()->willReturn('title');
        $field->getValue()->willReturn('Other');
        $field->getType()->willReturn(Field::TYPE_STRING);
        $field->isStored()->willReturn(true);
        $field->isIndexed()->willReturn(true);
        $field->isAggregate()->willReturn(true);
        $field2 = $this->prophesize(Field::class);
        $field2->getName()->willReturn('description');
        $field2->getValue()->willReturn('Test ony once.');
        $field2->getType()->willReturn(Field::TYPE_STRING);
        $field2->isStored()->willReturn(true);
        $field2->isIndexed()->willReturn(true);
        $field2->isAggregate()->willReturn(true);
        $document2->getFields()->willReturn([
            $field,
            $field2,
        ]);

        $adapter = new ZendLuceneAdapter(
            new Factory(),
            $this->dataPath,
            new Filesystem()
        );

        $adapter->index($document1->reveal(), 'foo_sorting');
        $adapter->index($document2->reveal(), 'foo_sorting_2');

        $query = new SearchQuery('Test');
        $query->setIndexes(['foo_sorting', 'foo_sorting_2']);

        $searchResult = $adapter->search($query);

        $itemIds = [];
        foreach ($searchResult as $document) {
            $itemIds[] = $document->getId();
        }

        $this->assertSame(['1', '2'], $itemIds);
    }

    /**
     * If the field is aggregate, its value should be aggregated into the aggregate field.
     */
    public function testIndexWithNullValues()
    {
        $adapter = $this->createAdapter($this->dataPath);
        $this->document->getId()->willReturn(12);
        $this->document->getIndex()->willReturn('foo');
        $this->document->getFields()->willReturn(
            [
                $this->field1,
                $this->field2,
            ]
        );

        foreach (['field1', 'field2'] as $fieldName) {
            $this->$fieldName->getName()->willReturn($fieldName);
            $this->$fieldName->getValue()->willReturn(null);
            $this->$fieldName->getType()->willReturn(Field::TYPE_NULL);
        }

        $luceneDocument = $adapter->index($this->document->reveal(), 'foo');

        $this->assertNotContains('field1', $luceneDocument->getFieldNames());
        $this->assertNotContains('field2', $luceneDocument->getFieldNames());
    }

    /**
     * It should create the folder for the path if it does not exist.
     */
    public function testInitialize()
    {
        $adapter = $this->createAdapter($this->dataPath);

        $this->filesystem->exists($this->dataPath)->willReturn(false);
        $this->filesystem->mkdir($this->dataPath)->shouldBeCalled();

        $adapter->initialize();
    }

    /**
     * It should not create the folder for the path if it does not exist.
     */
    public function testInitializeWithExistingFolder()
    {
        $adapter = $this->createAdapter($this->dataPath);

        $this->filesystem->exists($this->dataPath)->willReturn(true);
        $this->filesystem->mkdir($this->dataPath)->shouldNotBeCalled();

        $adapter->initialize();
    }

    private function createAdapter($basePath)
    {
        $adapter = new ZendLuceneAdapter(
            $this->factory->reveal(),
            $basePath,
            $this->filesystem->reveal()
        );

        return $adapter;
    }
}
