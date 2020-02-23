<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Massive\Bundle\SearchBundle\Search\Event\DeindexEvent;
use Massive\Bundle\SearchBundle\Search\Event\IndexEvent;
use Massive\Bundle\SearchBundle\Search\EventSubscriber\DoctrineOrmSubscriber;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineOrmSubscriberTest extends TestCase
{
    public function testMapping()
    {
        $eventArgs = $this->prophesize(LifecycleEventArgs::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $subscriber = new DoctrineOrmSubscriber($eventDispatcher->reveal());

        foreach ($subscriber->getSubscribedEvents() as $eventName) {
            $subscriber->{$eventName}($eventArgs->reveal());
        }
    }

    public function testPostRemove()
    {
        $entity = new stdClass();
        $eventArgs = $this->prophesize(LifecycleEventArgs::class);
        $eventArgs->getEntity()->willReturn($entity);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $subscriber = new DoctrineOrmSubscriber($eventDispatcher->reveal());

        $subscriber->preRemove($eventArgs->reveal());

        $eventDispatcher->dispatch(
            Argument::that(
                function (DeindexEvent $event) use ($entity) {
                    $this->assertEquals($entity, $event->getSubject());

                    return true;
                }
            ),
            SearchEvents::DEINDEX
        )->shouldBeCalledTimes(1);
    }

    public function testPostUpdate()
    {
        $entity = new stdClass();
        $eventArgs = $this->prophesize(LifecycleEventArgs::class);
        $eventArgs->getEntity()->willReturn($entity);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $subscriber = new DoctrineOrmSubscriber($eventDispatcher->reveal());

        $subscriber->postUpdate($eventArgs->reveal());

        $eventDispatcher->dispatch(
            SearchEvents::INDEX,
            Argument::that(
                function (IndexEvent $event) use ($entity) {
                    $this->assertEquals($entity, $event->getSubject());

                    return true;
                }
            )
        )->shouldBeCalledTimes(1);
    }

    public function testPostPersist()
    {
        $entity = new stdClass();
        $eventArgs = $this->prophesize(LifecycleEventArgs::class);
        $eventArgs->getEntity()->willReturn($entity);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $subscriber = new DoctrineOrmSubscriber($eventDispatcher->reveal());

        $subscriber->postPersist($eventArgs->reveal());

        $eventDispatcher->dispatch(
            SearchEvents::INDEX,
            Argument::that(
                function (IndexEvent $event) use ($entity) {
                    $this->assertEquals($entity, $event->getSubject());

                    return true;
                }
            )
        )->shouldBeCalledTimes(1);
    }
}
