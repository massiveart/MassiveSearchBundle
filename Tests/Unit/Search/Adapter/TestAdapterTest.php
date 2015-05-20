<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\Adapter;

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
        $this->document1->setId(1);
        $this->document1->addField($this->factory->createField('foo', 'Foo'));
        $this->document2 = new Document();
        $this->document2->setId(2);
        $this->document2->addField($this->factory->createField('foo', 'Foo'));
    }

    public function testTestAdapter()
    {
        $this->adapter->index($this->document1, 'foo');
        $this->adapter->index($this->document2, 'foo');
        $query = new SearchQuery('Foo');
        $query->setIndexes(array('foo'));

        $res = $this->adapter->search($query);

        $this->assertCount(2, $res);
    }

    public function testDeindex()
    {
        $this->adapter->index($this->document1, 'foo');
        $this->adapter->index($this->document2, 'foo');

        $this->adapter->deindex($this->document1, 'foo');
        $this->assertCount(1, $this->adapter->getDocuments());
    }
}
