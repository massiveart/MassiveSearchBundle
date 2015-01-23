<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Integration;

use Massive\Bundle\SearchBundle\Search\Adapter\ElasticSearchAdapter;
use Massive\Bundle\SearchBundle\Search\Localization\NoopStrategy;

class ElasticSearchAdapterTest extends AdapterTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = new \Elasticsearch\Client();
        parent::setUp();
    }

    public function purgeIndex($indexName)
    {
        try {
            $this->client->indices()->delete(array('index' => $indexName));
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
        }

        $this->client->indices()->create(array('index' => $indexName));
    }

    public function flush($indexName)
    {
        $this->client->indices()->flush(array('index' => $indexName));
    }

    public function doGetAdapter()
    {
        return new ElasticSearchAdapter($this->getFactory(), new NoopStrategy(), $this->client);
    }
}
