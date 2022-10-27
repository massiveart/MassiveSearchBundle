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
 * Simple value object for representing properties which
 * should be accesible using the PropertyAccessor.
 */
class Property implements FieldInterface
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var mixed|null
     */
    private $condition;

    /**
     * @param mixed $property
     */
    public function __construct($property, $condition = null)
    {
        $this->property = $property;
        $this->condition = $condition;
    }

    /**
     * Return the name of the referenced propery.
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    public function getCondition()
    {
        return $this->condition;
    }
}
