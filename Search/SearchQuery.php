<?php

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Represents a search query with contextual information
 * 
 * @author Daniel Leech <daniel.leech@massiveart.com>
 */
class SearchQuery
{
    private $searchManager;
    private $queryString;
    private $locale;

    public function __construct($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Return the query string
     *
     * @return string
     */
    public function getQueryString() 
    {
        return $this->queryString;
    }

    /**
     * Return the locale
     *
     * @return string
     */
    public function getLocale() 
    {
        return $this->locale;
    }

    /**
     * Set the locale
     *
     * @param string
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Return the indexes to search in
     *
     * @return array
     */
    public function getIndexes() 
    {
        return $this->indexes;
    }

    /**
     * Set the indexes to search in
     *
     * @param array
     */
    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
    }
}
