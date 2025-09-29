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

    protected function setUp(): void
    {
        parent::setUp();
        $this->expressionLanguage = $this->prophesize('Symfony\Component\ExpressionLanguage\ExpressionLanguage');
        $this->expressionLanguage->evaluate(Argument::any(), Argument::any())->willReturn('this_was_evaluated');

        $this->fieldEvaluator = new FieldEvaluator($this->expressionLanguage->reveal());
    }

    public static function provideGetValue()
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

    public static function provideEvaluateCondition()
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
            'Not existing variable' => [
                [],
                'price > 50',
                false,
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

    public function testEvaluateConditionWithSyntaxError()
    {
        // Test the new SyntaxError handling - should return false instead of throwing
        $expressionLanguage = $this->prophesize('Symfony\Component\ExpressionLanguage\ExpressionLanguage');
        $expressionLanguage->evaluate(Argument::any(), Argument::any())
            ->willThrow(new \Symfony\Component\ExpressionLanguage\SyntaxError('Syntax error'));

        $fieldEvaluator = new FieldEvaluator($expressionLanguage->reveal());
        $result = $fieldEvaluator->evaluateCondition(['test' => true], 'invalid syntax');

        $this->assertFalse($result);
    }

    public static function provideGetPropertyValue()
    {
        return [
            // Test null check - should return null for non-objects/non-arrays
            'Null object' => [
                null,
                'someProperty',
                null,
            ],
            'String value (non-object)' => [
                'string_value',
                'someProperty',
                null,
            ],
            'Integer value (non-object)' => [
                123,
                'someProperty',
                null,
            ],
            'Boolean value (non-object)' => [
                true,
                'someProperty',
                null,
            ],

            // Test valid object property access
            'Object with string property' => [
                function() {
                    $product = new Product();
                    $product->title = 'Test Product';

                    return $product;
                },
                'title',
                'Test Product',
            ],
            'Object with null property' => [
                function() {
                    $product = new Product();
                    $product->description = null;

                    return $product;
                },
                'description',
                null,
            ],
            'Object with false boolean property' => [
                function() {
                    $product = new Product();
                    $product->isActive = false;

                    return $product;
                },
                'isActive',
                false,
            ],
            'Object with zero integer property' => [
                function() {
                    $product = new Product();
                    $product->quantity = 0;

                    return $product;
                },
                'quantity',
                0,
            ],
            'Object with float property' => [
                function() {
                    $product = new Product();
                    $product->price = 19.99;

                    return $product;
                },
                'price',
                19.99,
            ],
            'Object with array property' => [
                function() {
                    $product = new Product();
                    $product->tags = ['tag1', 'tag2', 'tag3'];

                    return $product;
                },
                'tags',
                ['tag1', 'tag2', 'tag3'],
            ],
            'Object with empty array property' => [
                function() {
                    $product = new Product();
                    $product->tags = [];

                    return $product;
                },
                'tags',
                [],
            ],
            'Object with empty string property' => [
                function() {
                    $product = new Product();
                    $product->title = '';

                    return $product;
                },
                'title',
                '',
            ],

            // Test method access through PropertyAccessor
            'Object with getter method access' => [
                function() {
                    $product = new Product();
                    $product->setTitle('Method Value');

                    return $product;
                },
                'title',
                'Method Value',
            ],
            'Object with different getter method' => [
                function() {
                    $product = new Product();
                    $product->setBody('Body Content');

                    return $product;
                },
                'body',
                'Body Content',
            ],

            // Test nested property access
            'Object with nested property' => [
                function() {
                    $product = new Product();
                    $category = new \stdClass();
                    $category->name = 'Electronics';
                    $product->category = $category;

                    return $product;
                },
                'category.name',
                'Electronics',
            ],

            // Test array access
            'Array access with bracket notation' => [
                ['name' => 'Test Name', 'value' => 123],
                '[name]',
                'Test Name',
            ],
        ];
    }

    /**
     * @dataProvider provideGetPropertyValue
     */
    public function testGetPropertyValue($object, string $property, $expectedValue)
    {
        // Handle callable objects (for lazy initialization)
        if (\is_callable($object)) {
            $object = $object();
        }

        $field = new Property($property);
        $result = $this->fieldEvaluator->getValue($object, $field);

        $this->assertSame($expectedValue, $result);
    }
}
