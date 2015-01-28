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
use Massive\Bundle\SearchBundle\Search\Metadata\Property;
use Massive\Bundle\SearchBundle\Search\ObjectToDocumentConverter;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ObjectToDocumentConverterTest extends ProphecyTestCase
{
    protected $indexMetadata;

    public function setUp()
    {
        parent::setUp();
        $this->expressionLanguage = new ExpressionLanguage();
        $this->factory = new Factory();
        $this->indexMetadata = new IndexMetadata('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product');
        $this->product = new Product();

        $this->converter = new ObjectToDocumentConverter(
            $this->factory,
            $this->expressionLanguage
        );
    }

    public function provideConversion()
    {
        return array(
            array(
                array(
                    'setIndexName' => 'foo',
                    'setIdField' => new Property('id'),
                    'setTitleField' => new Expression('object.title'),
                    'setDescriptionField' => new Property('body'),
                    'setUrlField' => new Expression('\'/admin/contacts/url/\' ~ object.id'),
                    'setImageUrlField' => new Property('image'),
                    'setLocaleField' => new Property('locale'),
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
                    'getUrl' => '/admin/contacts/url/66',
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
            $this->indexMetadata->{$methodName}($value);
        }

        $document = $this->converter->objectToDocument($this->indexMetadata, $this->product);

        foreach ($expected as $method => $expectedValue) {
            $this->assertEquals($expectedValue, $document->$method());
        }
    }
}

