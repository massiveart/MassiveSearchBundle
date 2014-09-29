<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber\TestSubscriber;

class SearchManagerTest extends BaseTestCase
{
    public function testSearchManager()
    {
        $nbResults = 10;

        $this->generateIndex($nbResults);
        $res = $this->getSearchManager()->createSearch('Hello*')->index('product')->execute();

        $this->assertCount($nbResults, $res);

        $res = $this->getSearchManager()->createSearch('Hello this is a product 1')->index('product')->execute();
        $this->assertCount(10, $res);

        // this is a full match with score = 1
        $this->assertEquals(1, $res[0]->getScore());

        $match = $res[0];
        $document = $match->getDocument();

        $this->assertEquals(1, $document->getId());
        $this->assertEquals('Hello this is a product 1', $document->getTitle());
        $this->assertEquals('To be or not to be, that is the question', $document->getDescription());
        $this->assertEquals('/foobar', $document->getUrl());
        $this->assertEquals('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product', $document->getClass());
    }

    public function testEventDispatch()
    {
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $testSubscriber = new TestSubscriber();
        $eventDispatcher->addSubscriber($testSubscriber);

        $this->generateIndex(1);

        $this->assertNull($testSubscriber->hitDocument);
        $this->getSearchManager()->createSearch('Hello*')->index('product')->execute();
        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Document', $testSubscriber->hitDocument);

        $this->assertEquals(10, $testSubscriber->nbHits);

        // test HIT dispatch
        $this->getSearchManager()->createSearch('Hello*')->index('product')->execute();
        $this->assertEquals(20, $testSubscriber->nbHits);
        $this->assertInstanceOf('ReflectionClass', $testSubscriber->documentReflection);
        $this->assertEquals('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product', $testSubscriber->documentReflection->name);
        // test PRE_INDEX dispatch
        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface', $testSubscriber->preIndexMetadata);
        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Document', $testSubscriber->preIndexDocument);
    }
}
