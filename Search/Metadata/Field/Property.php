<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Field;

use Massive\Bundle\SearchBundle\Search\Metadata\FieldInterface;

/**
 * Simple value object for representing propertys
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Property implements FieldInterface
{
    /**
     * @var string
     */
    private $property;

    /**
     * @param mixed $property
     */
    public function __construct($property)
    {
        $this->property = $property;
    }

    /**
     * Return the name of the referenced propery
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }
}
