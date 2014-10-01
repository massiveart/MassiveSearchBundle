<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Symfony\Component\EventDispatcher\Event;
use Massive\Bundle\SearchBundle\Search\SearchQuery;

/**
 * Provides data for search event
 * @package Massive\Bundle\SearchBundle\Search\Event
 */
class SearchEvent extends Event
{
    /**
     * @var SearchQuery
     */
    protected $searchQuery;

    /**
     * @param SearchQuery $searchQuery
     */
    public function __construct(SearchQuery $searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }
} 
