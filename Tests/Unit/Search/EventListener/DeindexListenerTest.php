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

use Massive\Bundle\SearchBundle\Search\Event\DeindexEvent;
use Massive\Bundle\SearchBundle\Search\EventListener\DeindexListener;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use stdClass;

class DeindexListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnDeindex()
    {
        $entity = new stdClass();
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $event = $this->prophesize(DeindexEvent::class);
        $event->getSubject()->willReturn($entity);
        $listener = new DeindexListener($searchManager->reveal());

        $listener->onDeindex($event->reveal());

        $searchManager->deindex($entity)->shouldBeCalledTimes(1);
    }
}
