<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Reindex;

/**
 * ReindexProviders provide objects that should be reindexed.
 *
 * They are used by the ReindexCommand.
 */
interface ReindexProviderInterface
{
    /**
     * Return a $maxResults of objects from $offset for the given
     * $classFqn.
     *
     * @param string $classFqn
     * @param int $offset
     * @param int $maxResults
     *
     * @return object[]
     */
    public function provide($classFqn, $offset, $maxResults);

    /**
     * Return the total number of objects that need to be reindexed.
     *
     * @param string $classFqn
     *
     * @return int
     */
    public function getCount($classFqn);

    /**
     * Return all classes FQNs (fully qualified names) that require reindexing.
     *
     * @return array
     */
    public function getClassFqns();
}
