<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

/**
 * Simple value object for representing expressions
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Expression
{
    /**
     * @var string
     */
    private $expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Return the expression 
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }
}
