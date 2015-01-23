<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;

/**
 * Factory class for all new Search objects
 */
class Factory
{
    /**
     * Make a new search document
     *
     * @return Document
     */
    public function makeDocument()
    {
        return new Document();
    }

    /**
     * Make a new query hit
     *
     * @return QueryHit
     */
    public function makeQueryHit()
    {
        return new QueryHit();
    }

    /**
     * Make a new search field (fields are contained within
     * documents)
     *
     * @return Field
     */
    public function makeField($name, $value, $type = Field::TYPE_STRING)
    {
        return new Field($name, $value, $type);
    }

    /**
     * Make a new metadata object representing a mapped
     * searchable class
     *
     * @return IndexMetadata
     */
    public function makeIndexMetadata($class)
    {
        return new IndexMetadata($class);
    }
}
