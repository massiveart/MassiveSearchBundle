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
use Prophecy\Argument;

class ConverterManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $converter = $this->prophesize(ConverterInterface::class);
        $converter->convert([1, 2, 3])->willReturn('1,2,3');

        $converterManager = new ConverterManager();
        $converterManager->addConverter('tags', $converter->reveal());

        $result = $converterManager->convert([1, 2, 3], 'tags');

        $this->assertEquals('1,2,3', $result);
        $converter->convert([1, 2, 3])->shouldBeCalled();
    }

    public function testConverterNotExists()
    {
        $this->setExpectedException(ConverterNotFoundException::class);

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
