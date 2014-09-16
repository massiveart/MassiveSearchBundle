<?php

namespace Massive\MassiveSearchBundle\Tests\Unit\Search;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Document;

class DocumentTest extends ProphecyTestCase
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
}
