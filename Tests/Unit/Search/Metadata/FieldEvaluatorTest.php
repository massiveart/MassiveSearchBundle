<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Metadata;

use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Value;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldInterface;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Product;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FieldEvaluatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    protected function setUp()
    {
        parent::setUp();
        $this->expressionLanguage = $this->prophesize('Symfony\Component\ExpressionLanguage\ExpressionLanguage');
        $this->expressionLanguage->evaluate(Argument::any(), Argument::any())->willReturn('this_was_evaluated');

        $this->fieldEvaluator = new FieldEvaluator($this->expressionLanguage->reveal());
    }

    public function provideGetValue()
    {
        return [
            'Field with string value' => [
                new Field('title'),
                [
                    'title' => 'My product',
                ],
                'My product',
            ],
            'Property with string value' => [
                new Property('title'),
                [
                    'title' => 'My product',
                ],
                'My product',
            ],
            'Expression' => [
                new Expression('object.title'),
                [
                    'title' => 'My product',
                ],
                'this_was_evaluated',
            ],
            'Field with integer value' => [
                new Field('price'),
                [
                    'price' => 1999,
                ],
                1999,
            ],
            'Property with boolean value' => [
                new Property('isActive'),
                [
                    'isActive' => true,
                ],
                true,
            ],
            'Field with array value' => [
                new Field('tags'),
                [
                    'tags' => ['tag1', 'tag2'],
                ],
                ['tag1', 'tag2'],
            ],
            'Property with null value' => [
                new Property('description'),
                [
                    'description' => null,
                ],
                null,
            ],
            'Field with object value' => [
                new Field('category'),
                [
                    'category' => 'Electronics',
                ],
                'Electronics',
            ],
        ];
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
        $this->assertSame($expectedValue, $result);
    }

    public function testGetValueWithValue()
    {
        // Create a real Value instance instead of a mock
        $valueField = new Value('static value');

        $result = $this->fieldEvaluator->getValue(new Product(), $valueField);
        $this->assertSame('static value', $result);
    }

    public function testGetValueWithArrayAndField()
    {
        $field = new Field('name');
        $data = ['name' => 'Array value'];

        $result = $this->fieldEvaluator->getValue($data, $field);
        $this->assertSame('Array value', $result);
    }

    public function testGetValueWithInvalidFieldType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown field type');

        $invalidField = $this->prophesize(FieldInterface::class)->reveal();
        $this->fieldEvaluator->getValue(new Product(), $invalidField);
    }

    public function testGetValueWithInvalidPropertyAccess()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error encountered when trying to determine value');

        $product = new Product();
        $field = new Property('nonexistentProperty');

        $this->fieldEvaluator->getValue($product, $field);
    }

    public function testGetExpressionValueWithError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error encountered when trying to determine value');

        $this->expressionLanguage->evaluate(Argument::any(), Argument::any())
            ->willThrow(new \Exception('Expression error'));

        $product = new Product();
        $field = new Expression('object.something');

        $this->fieldEvaluator->getValue($product, $field);
    }

    public function provideEvaluateCondition()
    {
        return [
            'True condition' => [
                ['age' => 25, 'active' => true],
                'age > 18 and active == true',
                true,
            ],
            'False condition' => [
                ['age' => 15, 'active' => true],
                'age > 18 and active == true',
                false,
            ],
            'Complex condition' => [
                ['price' => 100, 'discount' => 20, 'stock' => 5],
                'price > 50 and (discount > 10 or stock > 10)',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideEvaluateCondition
     */
    public function testEvaluateCondition(array $object, string $condition, bool $expected)
    {
        $expressionLanguage = new ExpressionLanguage();
        $fieldEvaluator = new FieldEvaluator($expressionLanguage);

        $result = $fieldEvaluator->evaluateCondition($object, $condition);
        $this->assertSame($expected, $result);
    }

    public function testEvaluateConditionWithNonArrayObject()
    {
        $product = new Product();
        $result = $this->fieldEvaluator->evaluateCondition($product, 'whatever');

        $this->assertFalse($result);
    }

    public function testEvaluateConditionWithError()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error encountered when evaluating expression');

        $this->expressionLanguage->evaluate(Argument::any(), Argument::any())
            ->willThrow(new \Exception('Expression error'));

        $this->fieldEvaluator->evaluateCondition(['test' => true], 'invalid_syntax');
    }
}
