<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Expression language for massive search bundle.
 */
class MassiveSearchExpressionLanguage extends ExpressionLanguage
{
    /**
     * {@inheritdoc}
     */
    protected function registerFunctions()
    {
        parent::registerFunctions();

        $this->addFunction($this->createJoinFunction());
        $this->addFunction($this->createMapFunction());
    }

    /**
     * Join is an alias for PHP implode:.
     *
     *   join(',', ['one', 'two', 'three']) = "one,two,three"
     *
     * @return ExpressionFunction
     */
    private function createJoinFunction()
    {
        return new ExpressionFunction(
            'join',
            function ($glue, $elements) {
                return sprintf('join(%s, %s)', $glue, $elements);
            },
            function (array $values, $glue, $elements) {
                return implode($glue, $elements);
            }
        );
    }

    /**
     * Map is an analogue for array_map. The callback
     * in the form of an expression. The nested expression has
     * one variable, "el".
     *
     * For example:
     *
     *   map({'foo': 'one', 'foo': 'two'}, 'el["foo"]'}) = array('one', 'two');
     *
     * @return ExpressionFunction
     */
    private function createMapFunction()
    {
        return new ExpressionFunction(
            'map',
            function ($elements, $expression) {
                throw new \Exception('Map function does not support compilation');
            },
            function (array $values, $elements, $expression) {
                if (empty($elements)) {
                    return [];
                }

                $result = [];

                foreach ($elements as $element) {
                    $result[] = $this->evaluate($expression, [
                        'el' => $element,
                    ]);
                }

                return $result;
            }
        );
    }
}
