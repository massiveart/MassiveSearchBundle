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
use Massive\Bundle\SearchBundle\Search\Converter\NoConverterFoundException;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConverterManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $converter = $this->prophesize(ConverterInterface::class);
        $converter->convert([1, 2, 3])->willReturn('1,2,3');

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('converter_id')->willReturn($converter->reveal());

        $converterManager = new ConverterManager($container->reveal());
        $converterManager->addConverter('array', 'converter_id');

        $result = $converterManager->convert([1, 2, 3], 'array', 'string');

        $this->assertEquals('1,2,3', $result);
        $converter->convert([1, 2, 3])->shouldBeCalled();
        $container->get('converter_id')->shouldBeCalledTimes(1);
    }

    public function testConverterNotExists()
    {
        $this->setExpectedException(NoConverterFoundException::class);

        $container = $this->prophesize(ContainerInterface::class);

        $converterManager = new ConverterManager($container->reveal());
        $converterManager->convert([1, 2, 3], 'array', 'string');

        $container->get(Argument::any())->shouldNotBeCalled();
    }

    public function testHasExtensionTrue()
    {
        $converter = $this->prophesize(ConverterInterface::class);
        $converter->convert([1, 2, 3])->willReturn('1,2,3');

        $container = $this->prophesize(ContainerInterface::class);

        $converterManager = new ConverterManager($container->reveal());
        $converterManager->addConverter('array', 'string', $converter->reveal());

        $this->assertTrue($converterManager->hasConverter('array', 'string'));
        $container->get(Argument::any())->shouldNotBeCalled();
    }

    public function testHasExtensionFalse()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $converterManager = new ConverterManager($container->reveal());

        $this->assertFalse($converterManager->hasConverter('array', 'string'));
        $container->get(Argument::any())->shouldNotBeCalled();
    }
}
