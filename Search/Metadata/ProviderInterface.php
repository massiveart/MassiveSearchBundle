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
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;

/**
 * ProviderInterface instances provide search metadata for object instances.
 *
 * Currently this metadata system is implemented side-by-side with the JMS metadata
 * loader which only provides support for loading metadata by class name, but does provide
 * extra features such as hierachrical class metadata resolution.
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
