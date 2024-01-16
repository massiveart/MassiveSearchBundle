<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Reindex;

use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ReindexProviderRegistryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReindexProviderRegistry
     */
    private $registry;

    /**
     * @var ReindexProviderInterface
     */
    private $provider1;

    /**
     * @var ReindexProviderInterface
     */
    private $provider2;

    public function setUp()
    {
        $this->registry = new ReindexProviderRegistry();

        $this->provider1 = $this->prophesize(ReindexProviderInterface::class);
        $this->provider2 = $this->prophesize(ReindexProviderInterface::class);
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
     */
    public function testAlreadyRegisteredProviderName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('has already been registered.');

        $this->registry->addProvider('foo', $this->provider1->reveal());
        $this->registry->addProvider('foo', $this->provider1->reveal());
    }
}
