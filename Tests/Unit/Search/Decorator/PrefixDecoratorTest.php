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
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Prophecy\Argument;

class PrefixDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexNameDecoratorInterface
     */
    private $otherDecorator;

    /**
     * @var PrefixDecorator
     */
    private $prefixDecorator;

    public function setUp()
    {
        $this->otherDecorator = $this->prophesize(IndexNameDecoratorInterface::class);

        $this->prefixDecorator = new PrefixDecorator(
            $this->otherDecorator->reveal(),
            'prefix'
        );
    }

    public function testDecorate()
    {
        /** @var Document $document */
        $document = $this->prophesize(Document::class);

        /** @var IndexMetadata $indexMetadata */
        $indexMetadata = $this->prophesize(IndexMetadata::class);

        $this->otherDecorator->decorate($indexMetadata, null, $document->reveal())->willReturn('my_index');

        $this->assertEquals(
            'prefix_my_index',
            $this->prefixDecorator->decorate($indexMetadata->reveal(), null, $document->reveal())
        );
    }

    public function testUndecorate()
    {
        $decoratedIndexName = 'prefix_my_index';
        $indexName = 'my_index';
        $this->otherDecorator->undecorate($indexName)->willReturn($indexName)->shouldBeCalled();
        $this->assertEquals(
            $indexName,
            $this->prefixDecorator->undecorate(
                $decoratedIndexName
            )
        );
    }

    public function testIsVariant()
    {
        $options = ['option' => 'value'];
        $this->otherDecorator->isVariant('my_index', 'my_index', $options)->willReturn(true);
        $this->otherDecorator->undecorate('my_index')->willReturn('my_index');
        $this->assertTrue($this->prefixDecorator->isVariant('my_index', 'prefix_my_index', $options));
    }

    public function testIsVariantWithWrongPrefix()
    {
        $this->otherDecorator->isVariant(Argument::any(), Argument::any(), Argument::any())->willReturn(true);
        $this->otherDecorator->undecorate('my_index')->willReturn('my_index');
        $this->assertFalse($this->prefixDecorator->isVariant('prefixed_my_index', 'my_index'));
    }

    public function testIsVariantWithoutPrefix()
    {
        $this->otherDecorator->isVariant('my_index', 'my_index')->willReturn(true);
        $this->otherDecorator->undecorate('my_index')->willReturn('my_index');
        $this->assertFalse($this->prefixDecorator->isVariant('my_index', 'my_index'));
    }

    public function testIsVariantWithNegativeOtherDecorator()
    {
        $this->otherDecorator->isVariant(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $this->assertFalse($this->prefixDecorator->isVariant('my_index', 'prefix_my_index'));
    }
}
