<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\Adapter\Elastic;

use Elasticsearch\Client;
use Massive\Bundle\SearchBundle\Search\Adapter\Elastic\ClientFactory;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $client = ClientFactory::create(['hosts' => ['localhost:9200']]);

        $this->assertInstanceOf(Client::class, $client);
    }
}
