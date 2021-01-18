<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\EventListener;

use Massive\Bundle\SearchBundle\Search\Event\DeindexEvent;
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
     */
    public function onDeindex(DeindexEvent $event)
    {
        try {
            $this->searchManager->deindex($event->getSubject());
        } catch (MetadataNotFoundException $ex) {
            // no metadata found => do nothing
        }
    }
}
