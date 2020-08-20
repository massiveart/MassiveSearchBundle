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

use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;

interface SearchManagerInterface
{
    /**
     * @param object $object
     *
     * @return ClassMetadata
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getMetadata($object);

    /**
     * Search with the given query string.
     *
     * @param SearchQuery
     *
     * @return SearchResult
     *
     * @throws \Exception
     */
    public function search(SearchQuery $searchQuery);

    /**
     * Create a search query builder.
     *
     * @param string $query
     *
     * @return SearchQueryBuilder
     */
    public function createSearch($query);

    /**
     * Attempt to index the given object.
     *
     * @param object $object
     */
    public function index($object);

    /**
     * Remove the given mapped object entry or document from
     * its corresponding index.
     *
     * @param Document|object $object
     */
    public function deindex($object);

    /**
     * Return an array of arbitrary information
     * about the current state of the adapter.
     *
     * @return array
     */
    public function getStatus();

    /**
     * Purges the index with the given name.
     *
     * @param string $indexName
     */
    public function purge($indexName);

    /**
     * Return a list of all the index names (according to the metadata).
     *
     * @return string[]
     */
    public function getIndexNames();

    /**
     * Flush the adapter.
     *
     * The manager should keep track of the indexes that need flushing.
     */
    public function flush();
}
