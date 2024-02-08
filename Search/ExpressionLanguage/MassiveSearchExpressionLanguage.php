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
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;

/**
 * Expression language for massive search bundle.
 */
class MassiveSearchExpressionLanguage extends ExpressionLanguage
{
    protected function registerFunctions()
    {
        parent::registerFunctions();

        $this->addFunction($this->createJoinFunction());
        $this->addFunction($this->createMapFunction());
        $this->addFunction($this->createValueFunction());
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
            function($glue, $elements) {
                return \sprintf('join(%s, %s)', $glue, $elements);
            },
            function(array $values, $glue, $elements) {
                return \implode($glue, $elements);
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
            function($elements, $expression) {
                throw new \Exception('Map function does not support compilation');
            },
            function(array $values, $elements, $expression) {
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

    /**
     * Value returns the value of the variable. If the variable does not exists a default will be returned.
     *
     * For example:
     *
     *   massive_search_value("expression", {"hidden": false}) = array('hidden' => true);
     *
     * @return ExpressionFunction
     */
    private function createValueFunction()
    {
        $accessor = new PropertyAccessor();

        return new ExpressionFunction(
            'massive_search_value',
            function($elements, $expression) {
                throw new \Exception('Value function does not support compilation');
            },
            function(array $values, $propertyPath, $default = null) use ($accessor) {
                try {
                    $value = $accessor->getValue($values, $propertyPath);
                    if (null !== $value) {
                        return $value;
                    }
                } catch (NoSuchIndexException $e) {
                    // settings can be stdClass as it needs to convert to {} instead of [] for frontend
                    // currently NoSuchIndexException will be thrown which we need to catch here and
                    // return the default value
                }

                return $default;
            }
        );
    }
}
