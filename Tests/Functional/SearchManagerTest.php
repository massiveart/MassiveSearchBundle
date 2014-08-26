<?php

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber\TestSubscriber;

class SearchManagerTest extends BaseTestCase
{
    public function testSearchManager()
    {
        $nbResults = 10;

        $this->generateIndex($nbResults);
        $res = $this->getSearchManager()->search('Hello*', 'product');

        $this->assertCount($nbResults, $res);

        $res = $this->getSearchManager()->search('Hello this is a product 1', 'product');
        $this->assertCount(10, $res);

        // this is a full match with score = 1
        $this->assertEquals(1, $res[0]->getScore());

        $match = $res[0];
        $document = $match->getDocument();

        $this->assertEquals(1, $document->getId());
        $this->assertEquals('Hello this is a product 1', $document->getTitle());
        $this->assertEquals('To be or not to be, that is the question', $document->getDescription());
        $this->assertEquals('/foobar', $document->getUrl());
    }

    public function testHitEventDispatch()
    {
        $this->generateIndex(1);

        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $testSubscriber = new TestSubscriber();
        $eventDispatcher->addSubscriber($testSubscriber);

        $this->assertNull($testSubscriber->hitDocument);
        $this->getSearchManager()->search('Hello*', 'product');
        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Document', $testSubscriber->hitDocument);

        $this->assertEquals(10, $testSubscriber->nbHits);

        $this->getSearchManager()->search('Hello*', 'product');

        $this->assertEquals(20, $testSubscriber->nbHits);
    }
}
