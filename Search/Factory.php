<?php

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\QueryHit;

class Factory
{
    public function makeDocument()
    {
        return new Document();
    }

    public function makeQueryHit()
    {
        return new QueryHit();
    }

    public function makeField($name, $value, $type = Field::TYPE_STRING)
    {
        return new Field($name, $value, $type);
    }

    public function makeIndexMetadata($class)
    {
        return new IndexMetadata($class);
    }
}
