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
 * Interface to be implement by all search library adapters
 *
 * @author Daniel Leech <daniel.leech@massiveart.com>
 */
interface AdapterInterface
{
    /**
     * Index the given Document object
     *
     * @param Document $document Document to index
     * @param string $indexName Name of index to store document in
     */
    public function index(Document $document, $indexName);

    /**
     * Remove the given Document from the index
     *
     * @param Document $document
     * @param string $indexName
     */
    public function deindex(Document $document, $indexName);

    /**
     * Search using the given query string
     *
     * @param string $queryString
     */
    public function search(SearchQuery $searchQuery);

    /**
     * Return vendor status information as an associative
     * array.
     *
     * @return array
     */
    public function getStatus();
}
