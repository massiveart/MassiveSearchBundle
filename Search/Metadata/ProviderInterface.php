<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

use Massive\Bundle\SearchBundle\Search\Document;

/**
 * Provide a more flexible extension point for loading metadata.
 */
interface ProviderInterface
{
    /**
     * Load metadata for the given object
     *
     * @param object $object
     */
    public function getMetadataForObject($object);

    /**
     * Return all metadata instances
     *
     * @return ClassMetadata[]
     */
    public function getAllMetadata();

    /**
     * Return metadata for the given document
     *
     * @return ClassMetadata
     */
    public function getMetadataForDocument(Document $document);
}
