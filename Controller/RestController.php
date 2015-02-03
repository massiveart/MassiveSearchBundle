<?php
/*
 * This file is part of the Massive CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Controller;

use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * REST API for search
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RestController
{
    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @param SearchManagerInterface $searchManager
     */
    public function __construct(SearchManagerInterface $searchManager)
    {
        $this->searchManager = $searchManager;
    }

    /**
     * Perform a search and return a JSON response
     *
     * @param mixed $query Search query
     * @param array $indexes (optional) list of indexes
     * @param mixed $locale (optional) locale to search in
     */
    public function searchAction($query, $indexes = array(), $locale = null)
    {
        $hits = $this->searchManager
            ->createSearch($query)
            ->locale($locale)
            ->indexes($indexes)
            ->execute();

        return new JsonResponse($hits);
    }
}
