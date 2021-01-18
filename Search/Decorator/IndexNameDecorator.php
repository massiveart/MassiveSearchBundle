<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Decorator;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;

/**
 * The basic decorator, which just reads the values from the metadata.
 */
class IndexNameDecorator implements IndexNameDecoratorInterface
{
    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    public function __construct(FieldEvaluator $fieldEvaluator)
    {
        $this->fieldEvaluator = $fieldEvaluator;
    }

    public function decorate(IndexMetadataInterface $indexMetadata, $object, Document $document)
    {
        return $this->fieldEvaluator->getValue($object, $indexMetadata->getIndexName());
    }

    public function undecorate($decoratedIndexName)
    {
        return $decoratedIndexName;
    }

    public function isVariant($indexName, $decoratedIndexName, array $options = [])
    {
        return $indexName === $decoratedIndexName;
    }
}
