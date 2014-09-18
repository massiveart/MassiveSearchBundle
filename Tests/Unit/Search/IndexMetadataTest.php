<?php

namespace Unit\Search;

use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;

class IndexMetadataTest extends \PHPUnit_Framework_TestCase
{
    protected $indexMetadata;

    public function setUp()
    {
        $this->metadata = new IndexMetadata('\stdClass');
    }

    public function testGetSet()
    {
        $this->metadata->setImageUrlField('field');
        $this->assertEquals('field', $this->metadata->getImageUrlField());
    }
}
