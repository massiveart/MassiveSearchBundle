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
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;
use Prophecy\Argument;

class LocalizationDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexNameDecorator
     */
    private $otherDecorator;

    /**
     * @var LocalizationDecorator
     */
    private $localizationDecorator;

    public function setUp()
    {
        $this->otherDecorator = $this->prophesize(IndexNameDecorator::class);

        $this->localizationDecorator = new LocalizationDecorator($this->otherDecorator->reveal());
    }

    public function provideDecorate()
    {
        return [
            ['hello', 'de', 'hello-de-i18n'],
            ['hello', null, 'hello'],
            ['', 'de', '-de-i18n'],
        ];
    }

    /**
     * @dataProvider provideDecorate
     */
    public function testDecorate($indexName, $locale, $expectedResult)
    {
        /** @var Document $document */
        $document = $this->prophesize(Document::class);
        $document->getLocale()->willReturn($locale);

        /** @var IndexMetadataInterface $indexMetadata */
        $indexMetadata = $this->prophesize(IndexMetadataInterface::class);

        $this->otherDecorator->decorate($indexMetadata, null, $document)->willReturn($indexName);

        $this->assertEquals(
            $expectedResult,
            $this->localizationDecorator->decorate($indexMetadata->reveal(), null, $document->reveal())
        );
    }

    public function provideUndecorate()
    {
        return [
            ['hello-en-i18n', 'hello'],
            ['hello-test-en-i18n', 'hello-test'],
            ['hello-test-en_us-i18n', 'hello-test'],
        ];
    }

    /**
     * @dataProvider provideUndecorate
     */
    public function testUndecorate($decoratedIndexName, $expectedResult)
    {
        $this->otherDecorator->undecorate($expectedResult)->willReturn($expectedResult)->shouldBeCalled();
        $this->assertEquals($expectedResult, $this->localizationDecorator->undecorate($decoratedIndexName));
    }

    public function provideIsVariant()
    {
        return [
            [
                'asdfasdf',
                'my_index',
                null,
                false,
            ],
            [
                'my_index-fr-i18n',
                'my_index',
                null,
                true,
            ],
            [
                'foo_bar_index-de_at-i18n',
                'foo_bar_index',
                null,
                true,
            ],
            [
                'foo_bar_index_de_at_i18n',
                'foo_bar_index',
                null,
                false,
            ],
            [
                'foo_bar_foo_index_de-at-i18n',
                'foo_bar_index',
                null,
                false,
            ],
            [
                'my_index-fr-i18n',
                'my_index',
                'fr',
                true,
            ],
            [
                'my_index-fr-i18n',
                'my_index',
                'de',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideIsVariant
     */
    public function testIsVariant($decoratedIndexName, $indexName, $locale, $expectedResult)
    {
        $reflectionMethod = new \ReflectionMethod(LocalizationDecorator::class, 'removeLocale');
        $reflectionMethod->setAccessible(true);

        $options = ['locale' => $locale];
        $this->otherDecorator->isVariant(
            $indexName,
            $reflectionMethod->invoke($this->localizationDecorator, $decoratedIndexName),
            $options
        )->willReturn(true);

        $this->assertSame(
            $expectedResult,
            $this->localizationDecorator->isVariant($indexName, $decoratedIndexName, $options)
        );
    }

    public function testIsVariantWithNegativeOtherDecorator()
    {
        $this->otherDecorator->isVariant(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $this->assertFalse($this->localizationDecorator->isVariant('my_index', 'my_index-de-i18n'));
    }
}
