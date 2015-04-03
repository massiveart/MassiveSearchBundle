<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

        $this->metadata->setCategoryName('cat_1');
        $this->assertEquals('cat_1', $this->metadata->getCategoryName());
    }
}
