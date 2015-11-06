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
     * @param string $locale
     *
     * @return string
     */
    public function localizeIndexName($indexName, $locale);

    /**
     * Remove the localization part of the given index name.
     *
     * @param $indexName
     *
     * @return string
     */
    public function delocalizeIndexName($indexName);

    /**
     * Check to see if a given variant name is a variant of the
     * given indexName. This is used to determine which indexes can
     * be grouped together (e.g. when purging an index).
     *
     * @param string $indexName
     * @param string $variantName
     * @param string $locale
     *
     * @return bool
     */
    public function isIndexVariantOf($indexName, $variantName, $locale = null);
}
