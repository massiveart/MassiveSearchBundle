<?php

namespace Unit\Search\Adapter;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;

class ZendLuceneAdapterTest extends ProphecyTestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = $this->prophesize('Massive\Bundle\SearchBundle\Search\Factory');
    }

    /**
     * Listing indexes when the path does not exist should return an empty array
     */
    public function testListIndexesNotExist()
    {
        $adapter = $this->createAdapter('/path-not-exist');
        $result = $adapter->listIndexes();
        $this->assertEquals(array(), $result);
    }

    private function createAdapter($basePath)
    {
        $adapter = new ZendLuceneAdapter(
            $this->factory->reveal(),
            $basePath
        );

        return $adapter;
    }
}
