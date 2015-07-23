<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Provider;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface;

/**
 * Chain provider.
 */
class ChainProvider implements ProviderInterface
{
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForObject($object)
    {
        foreach ($this->providers as $provider) {
            $metadata = $provider->getMetadataForObject($object);

            if (null !== $metadata) {
                return $metadata;
            }
        }

        return;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllMetadata()
    {
        $metadatas = [];
        foreach ($this->providers as $provider) {
            foreach ($provider->getAllMetadata() as $metadata) {
                $metadatas[] = $metadata;
            }
        }

        return $metadatas;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForDocument(Document $document)
    {
        foreach ($this->providers as $provider) {
            $metadata = $provider->getMetadataForDocument($document);

            if (null !== $metadata) {
                return $metadata;
            }
        }

        return;
    }
}
