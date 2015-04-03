<?php

namespace Massive\Bundle\SearchBundle\Unit\Search\EventSubscriber;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\ExpressionLanguage\MassiveSearchExpressionLanguage;

class MassiveSearchExpressionLanguageTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->expressionLanguage = new MassiveSearchExpressionLanguage();
    }

    public function provideExpression()
    {
        return array(
            array(
                'join(" ", [ "one", "two", "three" ])',
                'one two three',
            ),
            array(
                'join(" ", [])',
                '',
            ),
            array(
                'map([{"foo": "one"}, {"foo":"two"}, {"foo": "three"}], "el[\'foo\']")',
                array('one', 'two', 'three'),
            ),
        );
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
