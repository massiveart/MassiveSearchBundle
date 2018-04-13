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
 * Interface to be implement by all search library adapters.
 *
 * Note that adapters are not aware of localization
 */
interface AdapterInterface
{
    /**
     * Index the given Document object.
     *
     * @param Document $document Document to index
     * @param string $indexName Name of index to store document in
     */
    public function index(Document $document, $indexName);

    /**
     * Remove the given Document from the index.
     *
     * @param Document $document
     * @param string $indexName
     */
    public function deindex(Document $document, $indexName);

    /**
     * Purge the given index with the given locale.
     */
    public function purge($indexName);

    /**
     * Search using the given query string.
     *
     * @param SearchQuery $searchQuery
     *
     * @return SearchResult
     */
    public function search(SearchQuery $searchQuery);

    /**
     * Return vendor status information as an associative
     * array.
     *
     * @return array
     */
    public function getStatus();

    /**
     * List all index names in the search implementation.
     *
     * @return array
     */
    public function listIndexes();

    public function flush(array $indexNames);

    /**
     * Initializes whatever is required for this adapter.
     */
    public function initialize();
}
