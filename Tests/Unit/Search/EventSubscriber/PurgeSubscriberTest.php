<?php
/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\EventSubscriber;

use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\EventSubscriber\PurgeSubscriber;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;

class PurgeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var PurgeSubscriber
     */
    private $purgeSubscriber;

    public function setUp()
    {
        $this->searchManager = $this->prophesize(SearchManagerInterface::class);
        $this->purgeSubscriber = new PurgeSubscriber($this->searchManager->reveal());
    }

    public function testPurgeIndexes()
    {
        $event = new IndexRebuildEvent(null, true);

        $this->searchManager->getIndexNames()->willReturn(['index1', 'index2']);
        $this->searchManager->purge('index1')->shouldBeCalled();
        $this->searchManager->purge('index2')->shouldBeCalled();

        $this->purgeSubscriber->purgeIndexes($event);
    }

    public function testPurgeIndexesWithoutPurgeOption()
    {
        $event = new IndexRebuildEvent(null, false);

        $this->searchManager->getIndexNames()->willReturn(['index1', 'index2']);
        $this->searchManager->purge('index1')->shouldNotBeCalled();
        $this->searchManager->purge('index2')->shouldNotBeCalled();

        $this->purgeSubscriber->purgeIndexes($event);
    }
}
