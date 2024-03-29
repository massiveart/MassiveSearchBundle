<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Unit\Search\Metadata;

use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ClassMetadataTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var IndexMetadata
     */
    private $indexMetadata;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    public function setUp()
    {
        $this->indexMetadata = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata');
        $this->classMetadata = new ClassMetadata('\stdClass');
    }

    public function testAddIndexExisting()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Context name "foo_context" has already been registered');

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
        $this->assertEquals(['foo_context', 'foo_bar'], \array_keys($indexMetadatas));
    }

    public function testSerializeUnserialize()
    {
        $this->classMetadata->setReindexRepositoryMethod('findSpecificEntities');

        $this->assertEquals($this->classMetadata, \unserialize(\serialize($this->classMetadata)));
    }
}
