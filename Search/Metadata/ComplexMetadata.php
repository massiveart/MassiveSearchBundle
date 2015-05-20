<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

/**
 * Metadata for fields (typically this would be used to represent
 * structured array elements in a normal search document metadata object.
 */
class ComplexMetadata
{
    /**
     * @var array
     */
    private $fieldMapping = array();

    /**
     * Return the field mapping for the complex data.
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * Set the field mapping.
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * Add some field mapping.
     */
    public function addFieldMapping($name, $mapping)
    {
        $this->fieldMapping[$name] = $mapping;
    }
}
