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
 * Localizaton strategy interface
 */
interface LocalizationStrategyInterface
{
    /**
     * Provide localized version of the given index name
     *
     * @param string $indexName
     */
    public function localizeIndexName($indexName, $locale);

    /**
     * Check to see if the candidate index name is a localized version of the
     * base index name. This is used when purging indexes.
     *
     * @param string $baseIndexName
     * @param string $candidateIndexName
     *
     * @return boolean
     */
    public function isLocalizedIndexNameOf($baseIndexName, $candidateIndexName);
}
