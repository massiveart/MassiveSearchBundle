<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Massive\Bundle\SearchBundle\Search\Event\DeindexEvent;
use Massive\Bundle\SearchBundle\Search\Event\IndexEvent;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Index mapped doctrine ORM documents.
 */
class DoctrineOrmSubscriber implements EventSubscriber
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
            Events::postUpdate,
            Events::postPersist,
        ];
    }

    /**
     * Index entities after they have been persisted.
     *
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->indexEntity($event->getEntity());
    }

    /**
     * Index entites after the have been updated.
     *
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->indexEntity($event->getEntity());
    }

    /**
     * Deindex entities after they have been removed.
     *
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $event = new DeindexEvent($event->getEntity());
        $this->eventDispatcher->dispatch($event, SearchEvents::DEINDEX);
    }

    /**
     * @param mixed $entity
     */
    private function indexEntity($entity)
    {
        $event = new IndexEvent($entity);
        $this->eventDispatcher->dispatch($event, SearchEvents::INDEX);
    }
}
