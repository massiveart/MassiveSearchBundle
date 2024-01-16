<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\EventListener;

use Massive\Bundle\SearchBundle\Search\Event\IndexEvent;
use Massive\Bundle\SearchBundle\Search\EventListener\IndexListener;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;

class IndexListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnIndex()
    {
        $entity = new stdClass();
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $event = $this->prophesize(IndexEvent::class);
        $event->getSubject()->willReturn($entity);
        $listener = new IndexListener($searchManager->reveal());

        $listener->onIndex($event->reveal());

        $searchManager->index($entity)->shouldBeCalledTimes(1);
    }
}
