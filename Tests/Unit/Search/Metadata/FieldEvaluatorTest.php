<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Metadata;

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\ObjectToDocumentConverter;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Prophecy\Argument;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldInterface;

class FieldEvaluatorTest extends ProphecyTestCase
{
    /**
     * @var ObjectToDocumentConverter
     */
    private $fieldEvaluator;

    public function setUp()
    {
        parent::setUp();
        $this->expressionLanguage = $this->prophesize('Symfony\Component\ExpressionLanguage\ExpressionLanguage');
        $this->expressionLanguage->evaluate(Argument::any(), Argument::any())->willReturn('this_was_evaluated');

        $this->fieldEvaluator = new FieldEvaluator($this->expressionLanguage->reveal());
    }

    public function provideGetValue()
    {
        return array(
            array(
                new Field('title'),
                array(
                    'title' => 'My product',
                ),
                'My product',
            ),
            array(
                new Property('title'),
                array(
                    'title' => 'My product',
                ),
                'My product',
            ),
            array(
                new Expression('object.title'),
                array(
                    'title' => 'My product',
                ),
                'this_was_evaluated',
            ),
        );
    }

    /**
     * @dataProvider provideGetValue
     */
    public function testGetValue(FieldInterface $field, $data, $expectedValue)
    {
        $product = new Product();
        foreach ($data as $key => $value) {
            $product->$key = $value;
        }

        $result = $this->fieldEvaluator->getValue($product, $field);
        $this->assertEquals($expectedValue, $result);
    }
}
