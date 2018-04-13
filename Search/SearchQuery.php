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
 * Represents a search query with contextual information.
 */
class SearchQuery
{
    // constants for search order
    const SORT_ASC = 'asc';

    const SORT_DESC = 'desc';

    /**
     * @var string
     */
    private $queryString;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    private $sortings = [];

    /**
     * @var array
     */
    private $indexes = [];

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset = 0;

    public function __construct($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Return the query string.
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Return the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the locale.
     *
     * @param string
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Return the indexes to search in.
     *
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Set the indexes to search in.
     *
     * @param array
     */
    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
    }

    /**
     * @return array
     */
    public function getSortings()
    {
        return $this->sortings;
    }

    /**
     * @param array $sortings
     */
    public function setSortings(array $sortings)
    {
        $this->sortings = $sortings;
    }

    /**
     * @param string $sort
     * @param string $order
     */
    public function addSorting($sort, $order = self::SORT_ASC)
    {
        $this->sortings[$sort] = $order;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
}
