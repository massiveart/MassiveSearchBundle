<?php

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber\TestSubscriber;

class SearchManagerTest extends BaseTestCase
{
    public function getSearchManager()
    {
        $searchManager = $this->getContainer()->get('massive_search.search_manager');
        return $searchManager;
    }

    private function generateIndex($nbResults)
    {
        $nbResults = 10;
        for ($i = 1; $i <= $nbResults; $i++) {
            $product = new Product();
            $product->setId($i);
            $product->setTitle('Hello this is a product '.$i);
            $product->setBody('To be or not to be, that is the question');

            $this->getSearchManager()->index($product);
        }
    }

    public function testSearchManager()
    {
        $nbResults = 10;

        $this->generateIndex($nbResults);
        $res = $this->getSearchManager()->search('Hello*', 'product');

        $this->assertCount($nbResults, $res);
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
