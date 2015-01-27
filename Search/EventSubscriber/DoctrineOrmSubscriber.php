<?php

namespace Massive\Bundle\SearchBundle\Search\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;

/**
 * Index mapped doctrine ORM documents
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DoctrineOrmSubscriber implements EventSubscriber
{
    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @param SearchManager $searchManager
     */
    public function __construct(SearchManagerInterface $searchManager)
    {
        $this->searchManager = $searchManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postRemove,
            Events::postUpdate,
            Events::postPersist
        );
    }

    /**
     * Index entities after they have been persisted
     *
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $this->indexEntity($entity);
    }

    /**
     * Index entites after the have been updated
     *
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $this->indexEntity($entity);
    }

    /**
     * Deindex entities after they have been removed
     *
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        try {
            $this->searchManager->deindex($entity);
        } catch (MetadataNotFoundException $e) {
            return;
        }
    }

    /**
     * @param mixed $entity
     */
    private function indexEntity($entity)
    {
        try {
            $this->searchManager->index($entity);
        } catch (MetadataNotFoundException $e) {
            return;
        }
    }

}
