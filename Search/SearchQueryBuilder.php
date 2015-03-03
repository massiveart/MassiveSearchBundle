<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Class used to fluently build a search query context
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
     * Set the index to search in
     *
     * @param string
     * @return SearchQueryBuilder
     */
    public function index($indexName)
    {
        return $this->indexes(array($indexName));
    }

    /**
     * Set the locale to search in
     *
     * @param string
     * @return SearchQueryBuilder
     */
    public function locale($locale)
    {
        $this->searchQuery->setLocale($locale);

        return $this;
    }

    /**
     * Set the indexes to search in
     *
     * @param string
     * @return SearchQueryBuilder
     */
    public function indexes(array $indexes)
    {
        $this->searchQuery->setIndexes($indexes);

        return $this;
    }

    /**
     * Execute the search
     *
     * @return array
     */
    public function execute()
    {
        return $this->searchManager->search($this->searchQuery);
    }
}
