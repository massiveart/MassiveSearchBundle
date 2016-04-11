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

use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurgeSubscriber implements EventSubscriberInterface
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SearchEvents::INDEX_REBUILD => ['purgeIndexes', 500],
        ];
    }

    /**
     * Purges all indexes, if the purge option is set.
     *
     * @param IndexRebuildEvent $event
     */
    public function purgeIndexes(IndexRebuildEvent $event)
    {
        if (!$event->getPurge()) {
            return;
        }

        foreach ($this->searchManager->getIndexNames() as $indexName) {
            $event->getOutput()->writeln('<info>Purging index</info>: ' . $indexName);
            $this->searchManager->purge($indexName);
        }
    }
}
