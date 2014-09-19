<?php

namespace Massive\Bundle\SearchBundle\Unit\Adapter;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Adapter\TestAdapter;
use Massive\Bundle\SearchBundle\Search\SearchQuery;

class TestAdapterTest extends ProphecyTestCase
{
    public function setUp()
    {
        $this->factory = new Factory();
        $this->adapter = new TestAdapter($this->factory);
        $this->document1 = new Document();
        $this->document2 = new Document();
    }

    public function testTestAdapter()
    {
        $this->adapter->index($this->document1, 'foo');
        $this->adapter->index($this->document2, 'foo');

        $res = $this->adapter->search(new SearchQuery('Anything'));

        $this->assertCount(2, $res);
    }
}
