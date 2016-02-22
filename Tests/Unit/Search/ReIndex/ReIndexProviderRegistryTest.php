<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\ReIndex;

use Massive\Bundle\SearchBundle\Search\ReIndex\ReIndexProviderInterface;
use Massive\Bundle\SearchBundle\Search\ReIndex\ReIndexProviderRegistry;

class ReIndexProviderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReIndexProviderRegistry
     */
    private $registry;

    /**
     * @var ReIndexProviderInterface
     */
    private $provider1;

    /**
     * @var ReIndexProviderInterface
     */
    private $provider2;

    public function setUp()
    {
        $this->registry = new ReIndexProviderRegistry();

        $this->provider1 = $this->prophesize(ReIndexProviderInterface::class);
        $this->provider2 = $this->prophesize(ReIndexProviderInterface::class);
    }

    /**
     * It should add a provider.
     * It should get a provider.
     * It should get all providers.
     */
    public function testAddProvider()
    {
        $this->registry->addProvider('foo', $this->provider1->reveal());
        $this->assertEquals($this->provider1->reveal(), $this->registry->getProvider('foo'));
        $this->assertEquals([
            'foo' => $this->provider1->reveal(),
        ], $this->registry->getProviders());
    }

    /**
     * It should throw an exception if a provider name has already been registered.'.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage has already been registered.
     */
    public function testAlreadyRegisteredProviderName()
    {
        $this->registry->addProvider('foo', $this->provider1->reveal());
        $this->registry->addProvider('foo', $this->provider1->reveal());
    }
}
