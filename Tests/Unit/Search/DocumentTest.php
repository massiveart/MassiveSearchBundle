<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\MassiveSearchBundle\Tests\Unit\Search;

use Massive\Bundle\SearchBundle\Search\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->field1 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Field');
        $this->field1->getName()->willReturn('field1');

        $this->document = new Document();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDocumentAddExistingField()
    {
        $this->document->addField($this->field1->reveal());
        $this->document->addField($this->field1->reveal());
    }

    public function testDocumentGetField()
    {
        $this->document->addField($this->field1->reveal());
        $res = $this->document->getField('field1');

        $this->assertSame($this->field1->reveal(), $res);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDocumentGetFieldNotExisting()
    {
        $res = $this->document->getField('field1');
    }

    public function testDocumentHasField()
    {
        $this->document->addField($this->field1->reveal());

        $this->assertTrue($this->document->hasField('field1'));
        $this->assertFalse($this->document->hasField('field2'));
    }

    public function testGetSet()
    {
        $this->document->setImageUrl('http://url.com/myimage.jpg');
        $this->assertEquals('http://url.com/myimage.jpg', $this->document->getImageUrl());

        $this->document->setCategory('cat_1');
        $this->assertEquals('cat_1', $this->document->getCategory());
    }
}
