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

use Massive\Bundle\SearchBundle\Search\Converter\ConverterManagerInterface;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Value;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\ObjectToDocumentConverter;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Product;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ObjectToDocumentConverterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var IndexMetadata
     */
    private $indexMetadata;

    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ObjectToDocumentConverter
     */
    private $converter;

    /**
     * @var ConverterManagerInterface
     */
    private $converterManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new Factory();
        $this->fieldEvaluator = $this->prophesize(FieldEvaluator::class);
        $this->converterManager = $this->prophesize(ConverterManagerInterface::class);
        $this->indexMetadata = new IndexMetadata();
        $this->product = new Product();

        $this->converter = new ObjectToDocumentConverter(
            $this->factory,
            $this->fieldEvaluator->reveal(),
            $this->converterManager->reveal()
        );
    }

    public static function provideConversion()
    {
        return [
            [
                [
                    'setIdField' => 'id',
                    'setTitleField' => 'title',
                    'setDescriptionField' => 'body',
                    'setUrlField' => 'url',
                    'setImageUrlField' => 'image',
                    'setLocaleField' => 'locale',
                ], [
                    'id' => '66',
                    'title' => 'My product',
                    'body' => 'Description of this',
                    'url' => '/path/to',
                    'image' => '/path/to/image',
                    'locale' => 'fr',
                ], [
                    'getImageUrl' => '/path/to/image',
                    'getDescription' => 'Description of this',
                    'getId' => '66',
                    'getTitle' => 'My product',
                    'getUrl' => '/path/to',
                    'getLocale' => 'fr',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideConversion
     */
    public function testConversion($metadata, $data, $expected)
    {
        foreach ($data as $key => $value) {
            $this->product->$key = $value;
        }

        $indexField = new Value('product');
        $this->indexMetadata->setIndexName($indexField);
        $this->fieldEvaluator->getValue($this->product, $indexField)->willReturn('product');
        foreach ($metadata as $methodName => $value) {
            $field = new Field($value);
            $this->indexMetadata->{$methodName}($field);
            $this->fieldEvaluator->getValue($this->product, $field)->willReturn($data[$value]);
        }

        $document = $this->converter->objectToDocument($this->indexMetadata, $this->product);

        foreach ($expected as $method => $expectedValue) {
            $this->assertEquals($expectedValue, $document->$method());
        }
    }

    /**
     * It should map the indexed, stored and aggregate fields.
     *
     * @dataProvider provideIndexStoredAndAggregate
     */
    public function testIndexedStoredAndAggregate($stored, $indexed, $aggregate)
    {
        $this->indexMetadata->setIdField(new Field('id'));
        $this->indexMetadata->setIndexName(new Value('product'));
        $this->indexMetadata->setFieldMapping([
            'title' => [
                'type' => 'string',
                'field' => new Property('title'),
                'stored' => $stored,
                'indexed' => $indexed,
                'aggregate' => $aggregate,
            ],
        ]);
        $document = $this->converter->objectToDocument($this->indexMetadata, $this->product);
        $field = $document->getField('title');

        $this->assertEquals($stored, $field->isStored());
        $this->assertEquals($indexed, $field->isIndexed());
        $this->assertEquals($aggregate, $field->isAggregate());
    }

    public static function provideIndexStoredAndAggregate()
    {
        return [
            [true, true, true],
            [false, false, false],
        ];
    }

    /**
     * It should throw an exception if an incomplete mapping is provided.
     */
    public function testMissingRequiredMapping()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not have');

        $this->indexMetadata->setIdField(new Field('id'));
        $this->indexMetadata->setIndexName(new Value('product'));
        $this->indexMetadata->setFieldMapping([
            'title' => [
            ],
        ]);
        $this->converter->objectToDocument($this->indexMetadata, $this->product);
    }

    /**
     * It should throw an exception if an incomplete mapping is provided for
     * a complex field.
     */
    public function testMissingRequiredMappingComplex()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('field mappings must have an additional');

        $this->indexMetadata->setIdField(new Field('id'));
        $this->indexMetadata->setIndexName(new Value('product'));
        $this->indexMetadata->setFieldMapping([
            'title' => [
                'type' => 'complex',
                'field' => new Property('title'),
            ],
        ]);
        $this->converter->objectToDocument($this->indexMetadata, $this->product);
    }

    public function testStripHtml()
    {
        $idField = new Field('id');
        $indexField = new Value('product');
        $this->indexMetadata->setIdField($idField);
        $this->indexMetadata->setIndexName($indexField);
        $titleExpression = 'object.getStructure().testTitle.getValue()';
        $descriptionExpression = 'object.getStructure().testDescription.getValue()';
        $titleField = new Expression($titleExpression);
        $descriptionField = new Expression($descriptionExpression);
        $this->indexMetadata->setTitleField($titleField);
        $this->indexMetadata->setDescriptionField($descriptionField);
        $this->indexMetadata->setFieldMapping([
            'testTitle' => [
                'field' => new Expression($titleExpression),
                'type' => 'string',
                'indexed' => false,
                'aggregate' => true,
                'html' => true,
            ],
            'testDescription' => [
                'field' => new Expression($descriptionExpression),
                'type' => 'string',
                'indexed' => false,
                'aggregate' => true,
                'html' => true,
            ],
        ]);
        $this->fieldEvaluator->getValue($this->product, $titleField)->willReturn('<p>Title</p>');
        $this->fieldEvaluator->getValue($this->product, $descriptionField)->willReturn('<p>Description</p>');
        $this->fieldEvaluator->getValue($this->product, $indexField)->willReturn('index');
        $this->fieldEvaluator->getValue($this->product, $idField)->willReturn('product');
        $document = $this->converter->objectToDocument($this->indexMetadata, $this->product);

        $this->assertSame('Title', $document->getTitle());
        $this->assertSame('Description', $document->getDescription());
    }
}
