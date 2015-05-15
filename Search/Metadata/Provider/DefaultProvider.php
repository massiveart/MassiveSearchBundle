<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Provider;

use Massive\Bundle\SearchBundle\Search\Document;
use Metadata\MetadataFactory;
use Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface;
use Metadata\MetadataFactoryInterface;

/**
 * Default metadata provider
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
     * {@inheritDoc}
     */
    public function getMetadataForObject($object)
    {
        return $this->metadataFactory->getMetadataForClass(get_class($object))->getOutsideClassMetadata();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllMetadata()
    {
        $classNames = $this->metadataFactory->getAllClassNames();
        $metadatas = array();
        foreach ($classNames as $className) {
            $metadatas[] = $this->metadataFactory->getMetadataForClass($className)->getOutsideClassMetadata();
        }

        return $metadatas;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForDocument(Document $document)
    {
        $className = $document->getClass();

        return $this->metadataFactory->getMetadataForClass($className)->getOutsideClassMetadata();
    }
}
