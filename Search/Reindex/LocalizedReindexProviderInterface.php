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
 * To be implemented by providers that provide localized objects for reindexing.
 */
interface LocalizedReindexProviderInterface extends ReindexProviderInterface
{
    /**
     * Return available locale codes for the given object.
     *
     * @param object $object
     *
     * @return string[]
     */
    public function getLocalesForObject($object);

    /**
     * Return the translated version of the given object.
     *
     * @param object $object
     * @param string $locale
     *
     * @return object
     */
    public function translateObject($object, $locale);
}
