<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Provides data for search event
 * @package Massive\Bundle\SearchBundle\Search\Event
 */
class SearchEvent extends Event
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var string[]
     */
    private $indexNames;

    function __construct($indexNames, $query)
    {
        $this->indexNames = $indexNames;
        $this->query = $query;
    }

    /**
     * @return \string[]
     */
    public function getIndexNames()
    {
        return $this->indexNames;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
} 
