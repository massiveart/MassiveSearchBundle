<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Decorator;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Value;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;

class IndexNameDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @var IndexNameDecorator
     */
    private $indexNameDecorator;

    public function setUp()
    {
        $this->fieldEvaluator = $this->prophesize(FieldEvaluator::class);

        $this->indexNameDecorator = new IndexNameDecorator($this->fieldEvaluator->reveal());
    }

    public function testDecorate()
    {
        /** @var Document $document */
        $document = $this->prophesize(Document::class);

        $indexField = new Value('my_index');

        /** @var IndexMetadataInterface $indexMetadata */
        $indexMetadata = $this->prophesize(IndexMetadataInterface::class);
        $indexMetadata->getIndexName()->willReturn($indexField);
        $this->fieldEvaluator->getValue(null, $indexField)->willReturn('my_index');

        $this->assertEquals(
            'my_index',
            $this->indexNameDecorator->decorate($indexMetadata->reveal(), null, $document->reveal())
        );
    }

    public function testUndecorate()
    {
        $this->assertEquals('my_index', $this->indexNameDecorator->undecorate('my_index'));
    }

    public function provideIsVariant()
    {
        return [
            [
                'asdfasdf',
                'my_index',
                false,
            ],
            [
                'my_index',
                'my_index',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideIsVariant
     */
    public function testIsVariant($decoratedIndexName, $indexName, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->indexNameDecorator->isVariant($indexName, $decoratedIndexName)
        );
    }
}
