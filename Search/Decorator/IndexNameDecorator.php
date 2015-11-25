<?php
/*
 * This file is part of Sulu.
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

    /**
     * Adds some decoration to the index name.
     *
     * @param IndexMetadataInterface $indexMetadata The metadata for the index
     * @param Document $document The document for which the decoration is done
     *
     * @return string
     */
    public function decorate(IndexMetadataInterface $indexMetadata, Document $document)
    {
        return $this->fieldEvaluator->getValue($document, $indexMetadata->getIndexName());
    }

    /**
     * Removes the added decoration from the decorate method.
     *
     * @param string $decoratedIndexName The decorated index name
     *
     * @return string
     */
    public function undecorate($decoratedIndexName)
    {
        return $decoratedIndexName;
    }

    /**
     * Checks if the index name can be decorated to the decorated index name by this decorator.
     *
     * @param string $indexName The undecorated index name
     * @param string $decoratedIndexName The given, decorated index name
     * @param array $options Additional options
     *
     * @return bool
     */
    public function isVariant($indexName, $decoratedIndexName, array $options = [])
    {
        return $indexName === $decoratedIndexName;
    }
}
