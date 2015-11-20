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
    const SEARCH_ORDER_ASC = 'desc';
    const SEARCH_ORDER_DESC = 'desc';

    /**
     * @var string
     */
    private $queryString;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $order = self::SEARCH_ORDER_ASC;

    /**
     * @var string
     */
    private $sort;

    /**
     * @var array
     */
    private $indexes = [];

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
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }
}
