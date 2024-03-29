<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\ExpressionLanguage;

use Massive\Bundle\SearchBundle\Search\ExpressionLanguage\MassiveSearchExpressionLanguage;
use PHPUnit\Framework\TestCase;

class MassiveSearchExpressionLanguageTest extends TestCase
{
    public function setUp()
    {
        $this->expressionLanguage = new MassiveSearchExpressionLanguage();
    }

    public function provideExpression()
    {
        return [
            [
                'join(" ", [ "one", "two", "three" ])',
                'one two three',
            ],
            [
                'join(" ", [])',
                '',
            ],
            [
                'map([{"foo": "one"}, {"foo":"two"}, {"foo": "three"}], "el[\'foo\']")',
                ['one', 'two', 'three'],
            ],
            [
                'massive_search_value("[three]")',
                null,
                ['one' => 'X', 'two' => 'Y'],
            ],
            [
                'massive_search_value("[three]", null)',
                null,
                ['one' => 'X', 'two' => 'Y'],
            ],
            [
                'massive_search_value("[three]", "default")',
                'default',
                ['one' => 'X', 'two' => 'Y'],
            ],
            [
                'massive_search_value("[three]", null)',
                'Z',
                ['one' => 'X', 'two' => 'Y', 'three' => 'Z'],
            ],
            [
                'massive_search_value("[three]", {"test": true})["test"]',
                true,
                ['one' => 'X', 'two' => 'Y'],
            ],
            [
                'massive_search_value("[three]", {"test": true})["test"]',
                false,
                ['one' => 'X', 'two' => 'Y', 'three' => ['test' => false]],
            ],
            [
                'massive_search_value("[three][test]", true)',
                true,
                ['one' => 'X', 'two' => 'Y'],
            ],
            [
                'massive_search_value("[XX][test]", true)',
                false,
                ['one' => 'X', 'two' => 'Y', 'XX' => ['test' => false]],
            ],
        ];
    }

    /**
     * @dataProvider provideExpression
     */
    public function testExpression($expression, $expectedResult, $values = [])
    {
        $result = $this->expressionLanguage->evaluate($expression, $values);
        $this->assertEquals($expectedResult, $result);
    }
}
