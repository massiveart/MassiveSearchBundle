<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Massive\Bundle\SearchBundle\Search\SearchQuery;

/**
 * Provides data for search event.
 */
class SearchEvent extends AbstractEvent
{
    /**
     * @var SearchQuery
     */
    protected $searchQuery;

    public function __construct(SearchQuery $searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return SearchQuery
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }
}
