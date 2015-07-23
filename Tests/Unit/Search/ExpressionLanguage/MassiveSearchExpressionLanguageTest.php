<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\EventSubscriber;

use Massive\Bundle\SearchBundle\Search\ExpressionLanguage\MassiveSearchExpressionLanguage;
use Prophecy\PhpUnit\ProphecyTestCase;

class MassiveSearchExpressionLanguageTest extends ProphecyTestCase
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
        ];
    }

    /**
     * @dataProvider provideExpression
     */
    public function testExpression($expression, $expectedResult)
    {
        $result = $this->expressionLanguage->evaluate($expression);
        $this->assertEquals($expectedResult, $result);
    }
}
