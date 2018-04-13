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
 * Adds the localization to the index name.
 */
class LocalizationDecorator implements IndexNameDecoratorInterface
{
    /**
     * @var IndexNameDecoratorInterface
     */
    private $decorator;

    public function __construct(IndexNameDecoratorInterface $decorator)
    {
        $this->decorator = $decorator;
    }

    /**
     * {@inheritdoc}
     */
    public function decorate(IndexMetadataInterface $indexMetadata, $object, Document $document)
    {
        $indexName = $this->decorator->decorate($indexMetadata, $object, $document);
        $locale = $document->getLocale();

        if (!$locale) {
            return $indexName;
        }

        return $indexName . '-' . $locale . '-i18n';
    }

    /**
     * {@inheritdoc}
     */
    public function undecorate($decoratedIndexName)
    {
        return $this->decorator->undecorate($this->removeLocale($decoratedIndexName));
    }

    /**
     * {@inheritdoc}
     */
    public function isVariant($indexName, $decoratedIndexName, array $options = [])
    {
        if (!$this->decorator->isVariant($indexName, $this->removeLocale($decoratedIndexName), $options)) {
            return false;
        }

        if ($indexName == $decoratedIndexName) {
            return true;
        }

        $locale = '[a-zA-Z_]+';
        if (isset($options['locale'])) {
            $locale = $options['locale'];
        }

        return (bool) preg_match(sprintf(
            '/^%s-%s-i18n$/',
            $indexName,
            $locale
        ), $decoratedIndexName);
    }

    /**
     * Removes the locale from the decorated index name.
     *
     * @param string $decoratedIndexName
     *
     * @return string
     */
    private function removeLocale($decoratedIndexName)
    {
        if (0 === preg_match('/(.*)(-.*-i18n)/', $decoratedIndexName, $matches)) {
            return $decoratedIndexName;
        }

        return $matches[1];
    }
}
