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
 * Simple value object for representing literals.
 */
class Value implements FieldInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the value of the field.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
