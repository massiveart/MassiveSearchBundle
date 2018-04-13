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
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;

/**
 * Prefixes the index names to distinguish multiple installations.
 */
class PrefixDecorator implements IndexNameDecoratorInterface
{
    /**
     * @var IndexNameDecoratorInterface
     */
    private $decorator;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(IndexNameDecoratorInterface $decorator, $prefix)
    {
        $this->decorator = $decorator;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function decorate(IndexMetadataInterface $indexMetadata, $object, Document $document)
    {
        $indexName = $this->decorator->decorate($indexMetadata, $object, $document);

        return $this->prefix . '_' . $indexName;
    }

    /**
     * {@inheritdoc}
     */
    public function undecorate($decoratedIndexName)
    {
        $decoratedIndexName = $this->removePrefix($decoratedIndexName);

        return $this->decorator->undecorate($decoratedIndexName);
    }

    /**
     * {@inheritdoc}
     */
    public function isVariant($indexName, $decoratedIndexName, array $options = [])
    {
        if (($indexName === $decoratedIndexName && $this->prefix) || 0 !== strpos($decoratedIndexName, $this->prefix)) {
            // if both names are the same, and a prefix is set the name was not decorated by this decorator
            return false;
        }

        $undecoratedIndexName = $this->removePrefix($decoratedIndexName);
        if (!$this->decorator->isVariant($indexName, $undecoratedIndexName, $options)) {
            return false;
        }

        return $indexName === $this->decorator->undecorate($undecoratedIndexName);
    }

    /**
     * Removes the prefix added by this decorator from the index name.
     *
     * @param $decoratedIndexName
     *
     * @return string
     */
    private function removePrefix($decoratedIndexName)
    {
        if (0 === strpos($decoratedIndexName, $this->prefix . '_')) {
            $decoratedIndexName = substr($decoratedIndexName, strlen($this->prefix) + 1);

            return $decoratedIndexName;
        }

        return $decoratedIndexName;
    }
}
