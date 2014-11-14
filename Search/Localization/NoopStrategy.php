<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Localization;

use Massive\Bundle\SearchBundle\Search\LocalizationStrategyInterface;

/**
 * No operation localization strategy
 *
 * Does ... nothing
 * Use with care
 */
class NoopStrategy implements LocalizationStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function localizeIndexName($indexName, $locale)
    {
        return $indexName;
    }
}
