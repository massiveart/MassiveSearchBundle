<?php

namespace Massive\Bundle\SearchBundle\Search\EventListener;

use Massive\Bundle\SearchBundle\Search\Event\IndexEvent;
use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;

/**
 * Listen on index event and call search manager.
 */
class IndexListener
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
     * Index subject from event.
     *
     * @param IndexEvent $event
     */
    public function onIndex(IndexEvent $event)
    {
        try {
            $this->searchManager->index($event->getSubject());
        } catch (MetadataNotFoundException $ex) {
            // no metadata found => do nothing
        }
    }
}
