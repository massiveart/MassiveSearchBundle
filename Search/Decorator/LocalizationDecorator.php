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

/**
 * Adds the localization to the index name.
 */
class LocalizationDecorator implements IndexNameDecoratorInterface
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
     * {@inheritdoc}
     */
    public function decorate(IndexMetadataInterface $indexMetadata, Document $document)
    {
        $indexName = $this->fieldEvaluator->getValue($document, $indexMetadata->getIndexName());
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
        if (preg_match('/(.*)(-.*-i18n)/', $decoratedIndexName, $matches) === 0) {
            return $decoratedIndexName;
        }

        return $matches[1];
    }

    /**
     * {@inheritdoc}
     */
    public function isVariant($indexName, $decoratedIndexName, array $options = [])
    {
        if ($indexName == $decoratedIndexName) {
            return true;
        }

        $locale = '[a-zA-Z_]+';
        if (isset($options['locale'])) {
            $locale = $options['locale'];
        }

        return (boolean) preg_match(sprintf(
            '/^%s-%s-i18n$/',
            $indexName,
            $locale
        ), $decoratedIndexName);
    }
}
