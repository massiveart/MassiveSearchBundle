<?php

namespace Unit\Search\Adapter;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;
use Massive\Bundle\SearchBundle\Search\Field;
use Symfony\Component\Filesystem\Filesystem;

class ZendLuceneAdapterTest extends ProphecyTestCase
{
    private $dataPath;
    private $factory;

    public function setUp()
    {
        $this->factory = $this->prophesize('Massive\Bundle\SearchBundle\Search\Factory');
        $this->document = $this->prophesize('Massive\Bundle\SearchBundle\Search\Document');
        $this->field1 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Field');
        $this->field2 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Field');
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
     * Listing indexes when the path does not exist should return an empty array
     */
    public function testListIndexesNotExist()
    {
        $adapter = $this->createAdapter('/path-not-exist');
        $result = $adapter->listIndexes();
        $this->assertEquals(array(), $result);
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
        $this->document->getFields()->willReturn(array(
            $this->field1
        ));

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
        return array(
            array(
                true,
                true,
                null
            ),
            array(
                false,
                true,
                null
            ),
            array(
                true,
                false,
                null
            ),
            array(
                false,
                false,
                array('\InvalidArgumentException', 'cannot be both not indexed and not stored'),
            )
        );
    }

    /**
     * If the field is aggregate, its value should be aggregated into the aggregate field
     */
    public function testIndexWithAggregate()
    {
        $adapter = $this->createAdapter($this->dataPath);
        $this->document->getId()->willReturn(12);
        $this->document->getFields()->willReturn(array(
            $this->field1,
            $this->field2,
        ));

        foreach (array('field1', 'field2') as $fieldName) {
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
