<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field as MetadataField;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Value;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;

/**
 * Factory class for all new Search objects.
 */
class Factory
{
    /**
     * Make a new search document.
     *
     * @return Document
     */
    public function createDocument()
    {
        return new Document();
    }

    /**
     * Make a new query hit.
     *
     * @return QueryHit
     */
    public function createQueryHit()
    {
        return new QueryHit();
    }

    /**
     * Make a new search field (fields are contained within
     * documents).
     *
     * @return Field
     */
    public function createField($name, $value, $type = Field::TYPE_STRING, $stored = true, $indexed = true, $aggregated = false)
    {
        return new Field($name, $value, $type, $stored, $indexed, $aggregated);
    }

    /**
     * Make a new metadata object representing a mapped
     * searchable class.
     */
    public function createClassMetadata($class)
    {
        return new ClassMetadata($class);
    }

    /**
     * Make a new metadata object representing an index for
     * a mapped searchable class.
     *
     * @return IndexMetadata
     */
    public function createIndexMetadata()
    {
        return new IndexMetadata();
    }

    public function createMetadataField($name)
    {
        return new MetadataField($name);
    }

    public function createMetadataProperty($path)
    {
        return new Property($path);
    }

    public function createMetadataValue($value)
    {
        return new Value($value);
    }

    public function createMetadataExpression($expression)
    {
        return new Expression($expression);
    }
}
