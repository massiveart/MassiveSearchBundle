<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Unit\Search\Converter;

use Massive\Bundle\SearchBundle\Search\Converter\ConverterInterface;
use Massive\Bundle\SearchBundle\Search\Converter\ConverterManager;
use Massive\Bundle\SearchBundle\Search\Converter\ConverterNotFoundException;
use Massive\Bundle\SearchBundle\Search\Document;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ConverterManagerTest extends TestCase
{
    public function testConvert()
    {
        $document = $this->prophesize(Document::class);
        $converter = $this->prophesize(ConverterInterface::class);
        $converter->convert([1, 2, 3], $document->reveal())->willReturn('1,2,3');

        $converterManager = new ConverterManager();
        $converterManager->addConverter('tags', $converter->reveal());

        $result = $converterManager->convert([1, 2, 3], 'tags', $document->reveal());

        $this->assertEquals('1,2,3', $result);
        $converter->convert([1, 2, 3], $document->reveal())->shouldBeCalled();
    }

    public function testConvertWithoutDocument()
    {
        $converter = $this->prophesize(ConverterInterface::class);
        $converter->convert([1, 2, 3], null)->willReturn('1,2,3');

        $converterManager = new ConverterManager();
        $converterManager->addConverter('tags', $converter->reveal());

        $result = $converterManager->convert([1, 2, 3], 'tags');

        $this->assertEquals('1,2,3', $result);
        $converter->convert([1, 2, 3], null)->shouldBeCalled();
    }

    public function testConverterNotExists()
    {
        $this->expectException(ConverterNotFoundException::class);

        $converterManager = new ConverterManager();
        $converterManager->convert([1, 2, 3], 'tags');
    }

    public function testHasExtensionTrue()
    {
        $converter = $this->prophesize(ConverterInterface::class);

        $converterManager = new ConverterManager();
        $converterManager->addConverter('tags', $converter->reveal());

        $this->assertTrue($converterManager->hasConverter('tags'));

        $converter->convert(Argument::any())->shouldNotBeCalled();
    }

    public function testHasExtensionFalse()
    {
        $converterManager = new ConverterManager();

        $this->assertFalse($converterManager->hasConverter('tags'));
    }
}
