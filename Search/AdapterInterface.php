<?php

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Interface to be implement by all search library adapters
 *
 * @author Daniel Leech <daniel.leech@massiveart.com>
 */
interface AdapterInterface
{
    /**
     * Index the given IndexEntry object
     *
     * @param Document $document Document to index
     * @param string $indexName Name of index to store document in
     */
    public function index(Document $document, $indexName);

    /**
     * Search using the given query string
     *
     * @param string $queryString
     */
    public function search($queryString, array $indexNames = array());
}
