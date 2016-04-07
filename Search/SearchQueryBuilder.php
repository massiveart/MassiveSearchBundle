<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Class used to fluently build a search query context.
 */
class SearchQueryBuilder
{
    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var SearchQuery
     */
    private $searchQuery;

    public function __construct(SearchManagerInterface $searchManager, SearchQuery $searchQuery)
    {
        $this->searchManager = $searchManager;
        $this->searchQuery = $searchQuery;
    }

    /**
     * Set the index to search in.
     *
     * @param string
     *
     * @return SearchQueryBuilder
     */
    public function index($indexName)
    {
        $this->searchQuery->setIndexes([$indexName]);

        return $this;
    }

    /**
     * Set the locale to search in.
     *
     * @param string
     *
     * @return SearchQueryBuilder
     */
    public function locale($locale)
    {
        $this->searchQuery->setLocale($locale);

        return $this;
    }

    /**
     * Set the indexes to search in.
     *
     * @param array $indexes
     *
     * @return SearchQueryBuilder
     */
    public function indexes(array $indexes)
    {
        $this->searchQuery->setIndexes($indexes);

        return $this;
    }

    /**
     * Set the sort Field.
     *
     * @param string $sort
     * @param string $order
     *
     * @return SearchQueryBuilder
     */
    public function addSorting($sort, $order = SearchQuery::SORT_ASC)
    {
        $this->searchQuery->addSorting($sort, $order);

        return $this;
    }

    /**
     * Set the limit.
     *
     * @param int $limit
     *
     * @return SearchQueryBuilder
     */
    public function setLimit($limit)
    {
        $this->searchQuery->setLimit($limit);

        return $this;
    }

    /**
     * Set the offset.
     * 
     * @param int $offset
     *
     * @return SearchQueryBuilder
     */
    public function setOffset($offset)
    {
        $this->searchQuery->setOffset($offset);

        return $this;
    }

    /**
     * Execute the search.
     *
     * @return SearchResult
     */
    public function execute()
    {
        return $this->searchManager->search($this->searchQuery);
    }
}
