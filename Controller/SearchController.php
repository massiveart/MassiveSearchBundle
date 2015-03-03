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
use Symfony\Component\HttpFoundation\Request;

/**
 * API controller for search
 */
class SearchController
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
     * @param Request $request
     */
    public function searchAction(Request $request)
    {
        $query = $request->query->get('q');
        $indexes = $request->query->get('indexes') ? : array();
        $locale = $request->query->get('locale') ? : null;

        $hits = $this->searchManager
            ->createSearch($query)
            ->locale($locale)
            ->indexes($indexes)
            ->execute();

        return new JsonResponse($hits);
    }
}
