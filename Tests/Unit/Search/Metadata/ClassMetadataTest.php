<?php

namespace Unit\Search\Metadata;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;

class ClassMetadataTest extends ProphecyTestCase
{
    private $indexMetadata;
    private $classMetadata;

    public function setUp()
    {
        $this->indexMetadata = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata');
        $this->classMetadata = new ClassMetadata('\stdClass');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Context name "foo_context" has already been registered
     */
    public function testAddIndexExisting()
    {
        $this->classMetadata->addIndexMetadata(
            'foo_context',
            $this->indexMetadata->reveal()
        );
        $this->classMetadata->addIndexMetadata(
            'foo_context',
            $this->indexMetadata->reveal()
        );
    }

    public function testAddIndex()
    {
        $this->classMetadata->addIndexMetadata(
            'foo_context',
            $this->indexMetadata->reveal()
        );
        $this->classMetadata->addIndexMetadata(
            'foo_bar',
            $this->indexMetadata->reveal()
        );

        $this->indexMetadata->setName('\stdClass')->shouldBeCalled();

        $indexMetadatas = $this->classMetadata->getIndexMetadatas();
        $this->assertEquals(array('foo_context', 'foo_bar'), array_keys($indexMetadatas));
    }
}
