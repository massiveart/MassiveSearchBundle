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

use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\ObjectToDocumentConverter;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;

class ObjectToDocumentConverterTest extends ProphecyTestCase
{
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

    public function setUp()
    {
        parent::setUp();
        $this->factory = new Factory();
        $this->fieldEvaluator = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator');
        $this->indexMetadata = new IndexMetadata('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product');
        $this->product = new Product();

        $this->converter = new ObjectToDocumentConverter(
            $this->factory,
            $this->fieldEvaluator->reveal()
        );
    }

    public function provideConversion()
    {
        return array(
            array(
                array(
                    'setIdField' => 'id',
                    'setTitleField' => 'title',
                    'setDescriptionField' => 'body',
                    'setUrlField' => 'url',
                    'setImageUrlField' => 'image',
                    'setLocaleField' => 'locale',
                ), array(
                    'id' => '66',
                    'title' => 'My product',
                    'body' => 'Description of this',
                    'url' => '/path/to',
                    'image' => '/path/to/image',
                    'locale' => 'fr',
                ), array(
                    'getClass' => 'Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product',
                    'getImageUrl' => '/path/to/image',
                    'getDescription' => 'Description of this',
                    'getId' => '66',
                    'getTitle' => 'My product',
                    'getUrl' => '/path/to',
                    'getLocale' => 'fr',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideConversion
     */
    public function testConversion($metadata, $data, $expected)
    {
        foreach ($data as $key => $value) {
            $this->product->$key = $value;
        }

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
}

