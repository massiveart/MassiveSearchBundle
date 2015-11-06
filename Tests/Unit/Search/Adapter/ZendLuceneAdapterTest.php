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
use Symfony\Component\Filesystem\Filesystem;

class ZendLuceneAdapterTest extends \PHPUnit_Framework_TestCase
{
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

    public function setUp()
    {
        $this->factory = $this->prophesize(Factory::class);
        $this->document = $this->prophesize(Document::class);
        $this->field1 = $this->prophesize(Field::class);
        $this->field2 = $this->prophesize(Field::class);
        $filesystem = new Filesystem();
        $this->dataPath = __DIR__ . '/../../../Resources/app/data';
        if (file_exists($this->dataPath)) {
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
            $this->setExpectedException($exceptionType, $exceptionMessage);
        }

        $adapter = $this->createAdapter($this->dataPath);
        $this->document->getId()->willReturn(12);
        $this->document->getFields()->willReturn([
            $this->field1,
        ]);
        $this->document->getIndex()->willReturn('foo');

        $this->field1->getName()->wilLReturn('hallo');
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
        $this->document->getFields()->willReturn([
            $this->field1,
            $this->field2,
        ]);

        foreach (['field1', 'field2'] as $fieldName) {
            $this->$fieldName->getName()->wilLReturn('hallo');
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

    private function createAdapter($basePath)
    {
        $adapter = new ZendLuceneAdapter(
            $this->factory->reveal(),
            $basePath
        );

        return $adapter;
    }
}
