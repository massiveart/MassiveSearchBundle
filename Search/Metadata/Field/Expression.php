<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Field;

use Massive\Bundle\SearchBundle\Search\Metadata\FieldInterface;

/**
 * Simple value object for representing expressions which
 * can be evaluated using the ExpressionLanguage.
 */
class Expression implements FieldInterface
{
    /**
     * @var string
     */
    private $expression;

    /**
     * @var string|null
     */
    private $condition;

    /**
     * @param string $expression
     */
    public function __construct($expression, $condition = null)
    {
        $this->expression = $expression;
        $this->condition = $condition;
    }

    /**
     * Return the expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function getCondition()
    {
        return $this->condition;
    }
}
