<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\Converter\ConverterManagerInterface;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;
use Massive\Bundle\SearchBundle\Search\Event\SearchEvent;
use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface as MetadataProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Search manager is the public API to the search
 * functionality.
 */
class SearchManager implements SearchManagerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var MetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ObjectToDocumentConverter
     */
    protected $converter;

    /**
     * @var LocalizationStrategyInterface
     */
    protected $localizationStrategy;

    /**
     * @var FieldEvaluator
     */
    protected $fieldEvaluator;

    /**
     * @var array
     */
    protected $indexesToFlush = [];

    public function __construct(
        AdapterInterface $adapter,
        MetadataProviderInterface $metadataProvider,
        ObjectToDocumentConverter $converter,
        EventDispatcherInterface $eventDispatcher,
        LocalizationStrategyInterface $localizationStrategy,
        FieldEvaluator $fieldEvaluator
    ) {
        $this->adapter = $adapter;
        $this->metadataProvider = $metadataProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->converter = $converter;
        $this->localizationStrategy = $localizationStrategy;
        $this->fieldEvaluator = $fieldEvaluator;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'You must pass an object to the %s method, you passed: %s',
                    __METHOD__,
                    var_export($object, true)
                )
            );
        }

        $metadata = $this->metadataProvider->getMetadataForObject($object);

        if (null === $metadata) {
            throw new MetadataNotFoundException(
                sprintf(
                    'There is no search mapping for object with class "%s"',
                    get_class($object)
                )
            );
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function deindex($object)
    {
        $metadata = $this->getMetadata($object);

        foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
            $indexName = $this->fieldEvaluator->getValue($object, $indexMetadata->getIndexName());
            $this->markIndexToFlush($indexName);
            $indexNames = $this->getLocalizedIndexNamesFor($indexName);

            foreach ($indexNames as $indexName) {
                $document = $this->converter->objectToDocument($indexMetadata, $object);
                $this->adapter->deindex($document, $indexName);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function index($object)
    {
        $indexMetadata = $this->getMetadata($object);

        foreach ($indexMetadata->getIndexMetadatas() as $indexMetadata) {
            $indexName = $this->fieldEvaluator->getValue($object, $indexMetadata->getIndexName());
            $this->markIndexToFlush($indexName);

            $document = $this->converter->objectToDocument($indexMetadata, $object);
            $evaluator = $this->converter->getFieldEvaluator();

            // if the index is locale aware, localize the index name
            if ($indexMetadata->getLocaleField()) {
                $indexName = $this->localizationStrategy->localizeIndexName($indexName, $document->getLocale());
            }

            $this->eventDispatcher->dispatch(
                SearchEvents::PRE_INDEX,
                new PreIndexEvent($object, $document, $indexMetadata, $evaluator)
            );

            $this->adapter->index($document, $indexName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createSearch($string)
    {
        return new SearchQueryBuilder($this, new SearchQuery($string));
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchQuery $query)
    {
        $this->validateQuery($query);
        $this->expandQueryIndexes($query);

        // At this point the indexes should have been expanded to potentially
        // include all indexes managed by massive search, if it is empty then
        // there is nothing to search for.
        //
        // See: https://github.com/massiveart/MassiveSearchBundle/issues/38
        if (0 === count($query->getIndexes())) {
            return [];
        }

        $this->eventDispatcher->dispatch(
            SearchEvents::SEARCH,
            new SearchEvent($query)
        );

        $hits = $this->adapter->search($query);

        /** @var QueryHit $hit */
        foreach ($hits as $hit) {
            $document = $hit->getDocument();

            // only throw events for existing documents
            if (!class_exists($document->getClass())) {
                continue;
            }

            $metadata = $this->metadataProvider->getMetadataForDocument($document);

            $this->eventDispatcher->dispatch(
                SearchEvents::HIT,
                new HitEvent($hit, $metadata)
            );
        }

        return $hits;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        $data = ['Adapter' => get_class($this->adapter)];
        $data += $this->adapter->getStatus() ?: [];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($indexName)
    {
        $this->markIndexToFlush($indexName);
        $indexes = $this->getLocalizedIndexNamesFor($indexName);
        foreach ($indexes as $indexName) {
            $this->adapter->purge($indexName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->adapter->flush(array_keys($this->indexesToFlush));
        $this->indexesToFlush = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexNames()
    {
        return array_unique(
            array_map(
                function ($indexName) {
                    return $this->localizationStrategy->delocalizeIndexName($indexName);
                },
                $this->adapter->listIndexes()
            )
        );
    }

    /**
     * Retrieve all the index names including localized names (i.e. variants)
     * for the given index name, optionally limiting to the given locale.
     *
     * @param string $indexName
     * @param string $locale
     *
     * @return string[]
     */
    private function getLocalizedIndexNamesFor($indexName, $locale = null)
    {
        $adapterIndexNames = $this->adapter->listIndexes();
        $indexNames = [];

        foreach ($adapterIndexNames as $adapterIndexName) {
            if ($this->localizationStrategy->isIndexVariantOf($indexName, $adapterIndexName, $locale)) {
                $indexNames[] = $adapterIndexName;
            }
        }

        return $indexNames;
    }

    /**
     * Add additional indexes to the Query object.
     *
     * If the query object has no indexes, then add all indexes (including
     * variants), otherwise expand the indexes the query does have to include
     * all of their variants.
     *
     * @param SearchQuery $query
     */
    private function expandQueryIndexes(SearchQuery $query)
    {
        $expandedIndexes = [];

        foreach ($query->getIndexes() as $index) {
            foreach ($this->getLocalizedIndexNamesFor($index, $query->getLocale()) as $expandedIndex) {
                $expandedIndexes[$expandedIndex] = $expandedIndex;
            }
        }

        $query->setIndexes($expandedIndexes);
    }

    /**
     * Mark an index to be flushed when "flush" is called.
     */
    private function markIndexToFlush($indexName)
    {
        $this->indexesToFlush[$indexName] = true;
    }

    /**
     * If query has indexes, ensure that they are known.
     *
     * @throws Exception\SearchException
     *
     * @param SearchQuery $query
     */
    private function validateQuery(SearchQuery $query)
    {
        $indexNames = $this->getIndexNames();
        $queryIndexNames = $query->getIndexes();

        foreach ($queryIndexNames as $queryIndexName) {
            if (!in_array($queryIndexName, $indexNames)) {
                $unknownIndexes[] = $queryIndexName;
            }
        }

        if (false === empty($unknownIndexes)) {
            throw new Exception\SearchException(
                sprintf(
                    'Search indexes "%s" not known. Known indexes: "%s"',
                    implode('", "', $queryIndexNames),
                    implode('", "', $indexNames)
                )
            );
        }
    }
}
