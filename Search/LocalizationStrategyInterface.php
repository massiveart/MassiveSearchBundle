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
 * Localizaton strategy interface.
 */
interface LocalizationStrategyInterface
{
    /**
     * Provide localized version of the given index name.
     *
     * @param string $indexName
     */
    public function localizeIndexName($indexName, $locale);

    /**
     * Check to see if a given variant name is a variant of the
     * given indexName. This is used to determine which indexes can
     * be grouped together (e.g. when purging an index).
     *
     * @param string $baseIndexName
     * @param string $candidateIndexName
     *
     * @return bool
     */
    public function isIndexVariantOf($indexName, $variantName);
}
