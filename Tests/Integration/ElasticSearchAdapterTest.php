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

    public function flush($indexName)
    {
        $this->client->indices()->flush(array(
            'index' => $indexName,
            'full' => true,
        ));

        // there is a timing issue, we need to pause for a while
        // after flusing for subsequent requests to not fail.
        usleep(50000);
    }

    public function doGetAdapter()
    {
        return new ElasticSearchAdapter($this->getFactory(), new NoopStrategy(), $this->client);
    }
}
