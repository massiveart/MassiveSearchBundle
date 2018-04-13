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
use Metadata\MetadataFactoryInterface;

/**
 * Default metadata provider.
 */
class DefaultProvider implements ProviderInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataForObject($object)
    {
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));

        if (null === $metadata) {
            return;
        }

        return $metadata->getOutsideClassMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMetadata()
    {
        $classNames = $this->metadataFactory->getAllClassNames();
        $metadatas = [];
        foreach ($classNames as $className) {
            $metadatas[] = $this->metadataFactory->getMetadataForClass($className)->getOutsideClassMetadata();
        }

        return $metadatas;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataForDocument(Document $document)
    {
        $className = $document->getClass();

        $metadata = $this->metadataFactory->getMetadataForClass($className);

        if (null === $metadata) {
            return;
        }

        return $metadata->getOutsideClassMetadata();
    }
}
