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
use Massive\Bundle\SearchBundle\Search\EventSubscriber\DoctrineOrmSubscriber;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Prophecy\PhpUnit\ProphecyTestCase;

class DoctrineOrmSubscriberTest extends ProphecyTestCase
{
    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @var object
     */
    private $entity;

    /**
     * @var LifecycleEventArgs
     */
    private $event;

    /**
     * @var DoctrineOrmSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $this->searchManager = $this->prophesize('Massive\Bundle\SearchBundle\Search\SearchManager');
        $this->entity = new \stdClass();
        $this->event = $this->prophesize('Doctrine\ORM\Event\LifecycleEventArgs');
        $this->event->getEntity()->willReturn($this->entity);

        $this->subscriber = new DoctrineOrmSubscriber($this->searchManager->reveal());
    }

    public function testMapping()
    {
        foreach ($this->subscriber->getSubscribedEvents() as $eventName) {
            $this->subscriber->{$eventName}($this->event->reveal());
        }
    }

    public function testPostRemove()
    {
        $this->searchManager->deindex($this->entity)->shouldBeCalled();
        $this->subscriber->preRemove($this->event->reveal());
    }

    public function testPostUpdate()
    {
        $this->searchManager->index($this->entity)->shouldBeCalled();
        $this->subscriber->postUpdate($this->event->reveal());
    }

    public function testPostPersist()
    {
        $this->searchManager->index($this->entity)->shouldBeCalled();
        $this->subscriber->postPersist($this->event->reveal());
    }
}
