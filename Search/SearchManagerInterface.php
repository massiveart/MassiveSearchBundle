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

use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;

interface SearchManagerInterface
{
    /**
     * @param object $object
     * @return IndexMetadataInterface
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getMetadata($object);

    /**
     * Return status information about the current implementation
     *
     * @return array
     */
    public function getStatus();

    /**
     * Search with the given query string
     *
     * @param SearchQuery
     * @return QueryHit[]
     * @throws \Exception
     */
    public function search(SearchQuery $searchQuery);

    /**
     * Create a search query builder
     *
     * @param string $queryString
     * @return SearchQueryBuilder
     */
    public function createSearch($query);

    /**
     * Attempt to index the given object
     *
     * @param object $object
     */
    public function index($object);

    /**
     * Index the object with given Metadata
     *
     * @param object $object
     * @param IndexMetadataInterface $metadata
     */
    public function indexWithMetadata($object, IndexMetadataInterface $metadata);
}
