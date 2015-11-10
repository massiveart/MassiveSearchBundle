<?php

namespace Massive\Bundle\SearchBundle\Search\EventListener;

use Massive\Bundle\SearchBundle\Search\Event\IndexEvent;
use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;

/**
 * Listen on index event and call search manager.
 */
class DeindexListener
{
    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    public function __construct(SearchManagerInterface $searchManager)
    {
        $this->searchManager = $searchManager;
    }

    /**
     * Deindex subject from event.
     * 
     * @param IndexEvent $event
     */
    public function onDeindex(IndexEvent $event)
    {
        try {
            $this->searchManager->deindex($event->getSubject());
        } catch (MetadataNotFoundException $ex) {
            // no metadata found => do nothing
        }
    }
}
