<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Adapter\Elastic;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * This factory encapsules client creation for elastic-search.
 */
class ClientFactory
{
    /**
     * This function create a new client for given config.
     *
     * @param array $config
     *
     * @return Client
     */
    public static function create($config)
    {
        return ClientBuilder::create()->setHosts($config['hosts'])->build();
    }
}
