<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\Event\SearchEvent;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Metadata\MetadataFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;
use Massive\Bundle\SearchBundle\Search\Exception\MetadataNotFoundException;

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
     * @var \Metadata\MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
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
     * @var array
     */
    protected $indexesToFlush = array();

    public function __construct(
        AdapterInterface $adapter,
        MetadataFactory $metadataFactory,
        ObjectToDocumentConverter $converter,
        EventDispatcherInterface $eventDispatcher,
        LocalizationStrategyInterface $localizationStrategy
    ) {
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->converter = $converter;
        $this->localizationStrategy = $localizationStrategy;
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

        return $this->getMetadataFor(get_class($object));
    }

    /**
     * {@inheritDoc}
     */
    public function deindex($object)
    {
        $metadata = $this->getMetadata($object);

        foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
            $this->markIndexToFlush($indexMetadata->getIndexName());
            $indexNames = $this->getLocalizedIndexNamesFor($indexMetadata->getIndexName());

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
        $indexMetadatas = $this->getMetadata($object);

        foreach ($indexMetadatas->getIndexMetadatas() as $indexMetadata) {
            $this->markIndexToFlush($indexMetadata->getIndexName());

            $indexName = $indexMetadata->getIndexName();

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
     * {@inheritDoc}
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

            $metadata = $this->getMetadataFor($document->getClass());
            $indexMetadata = $metadata->getIndexMetadata('_default');
            $document->setCategory($indexMetadata->getCategoryName());

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
        $data = array('Adapter' => get_class($this->adapter));
        $data += $this->adapter->getStatus() ? : array();

        return $data;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getCategoryNames()
    {
        $classNames = $this->metadataFactory->getAllClassNames();
        $categoryNames = array();

        foreach ($classNames as $className) {
            $metadata = $this->getMetadataFor($className);

            foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
                $categoryNames[] = $indexMetadata->getCategoryName();
            }
        }

        return array_values(array_unique($categoryNames));
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->adapter->flush(array_keys($this->indexesToFlush));
        $this->indexesToFlush = array();
    }

    /**
     * Return a list of all the index names (according to the metadata)
     *
     * If categories are specified, only return the indexes corresponding
     * to the given categories.
     *
     * @param array $categories
     *
     * @return string[]
     */
    public function getIndexNames($categories = null)
    {
        $classNames = $this->metadataFactory->getAllClassNames();
        $indexNames = array();

        foreach ($classNames as $className) {
            $metadata = $this->getMetadataFor($className);

            foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
                $indexName = $indexMetadata->getIndexName();
                if ($categories) {
                    if (in_array($indexMetadata->getCategoryName(), $categories)) {
                        $indexNames[$indexName] = $indexName;
                    }
                    continue;
                }

                $indexNames[$indexName] = $indexName;
            }
        }

        return array_values($indexNames);
    }

    /**
     * List all of the expanded index names in the search implementation
     * optionally only in the given locale.
     *
     * @param string $locale
     * @return string[]
     */
    private function getLocalizedIndexNames($locale = null)
    {
        $localizedIndexNames = array();
        $indexNames = $this->getIndexNames();

        foreach ($indexNames as $indexName) {
            foreach ($this->getLocalizedIndexNamesFor($indexName, $locale) as $localizedIndexName) {
                $localizedIndexNames[$localizedIndexName] = $localizedIndexName;
            }
        }

        return array_values($localizedIndexNames);
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
        $indexNames = array();

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
        $categories = $query->getCategories();

        if ($categories) {
            // if categories have been specified, override the indexes.
            // The two are mutually exlusive and this has already been
            // validated.
            $query->setIndexes($this->getIndexNames($categories));
        }

        if (!$query->getIndexes()) {
            $indexNames = $this->getLocalizedIndexNames($query->getLocale());
            $query->setIndexes($indexNames);
            return;
        }

        $expandedIndexes = array();

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
     * Return metadata for the given classname
     *
     * @param string $className
     *
     * @return ClassMetadata
     */
    private function getMetadataFor($className)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);

        if (null === $metadata) {
            throw new MetadataNotFoundException(
                sprintf(
                    'There is no search mapping for class "%s"',
                    $className
                )
            );
        }

        return $metadata->getOutsideClassMetadata();
    }

    /**
     * If query has indexes, ensure that they are known
     *
     * @throws Exception\SearchException
     * @param SearchQuery $query
     */
    private function validateQuery(SearchQuery $query)
    {
        $indexNames = $this->getIndexNames();
        $queryIndexNames = $query->getIndexes();
        $queryCategoryNames = $query->getCategories();
        $categoryNames = $this->getCategoryNames();

        if ($queryCategoryNames && $queryIndexNames) {
            throw new Exception\SearchException(sprintf(
                'Category and indexes are mutually exclusive, you specified categories "%s" and indexes "%s"',
                implode('", "', $queryCategoryNames), implode('", "', $queryIndexNames)
            ));
        }

        foreach ($queryIndexNames as $queryIndexName) {
            if (!in_array($queryIndexName, $indexNames)) {
                $unknownIndexes[] = $queryIndexName;
            }
        }

        if (false === empty($unknownIndexes)) {
            throw new Exception\SearchException(sprintf(
                'Search indexes "%s" not known. Known indexes: "%s"',
                implode('", "', $queryIndexNames), implode('", "', $indexNames)
            ));
        }

        foreach ($queryCategoryNames as $queryCategoryName) {
            if (!in_array($queryCategoryName, $categoryNames)) {
                $unknownCategories[] = $queryCategoryName;
            }
        }

        if (false === empty($unknownCategories)) {
            throw new Exception\SearchException(sprintf(
                'Categories "%s" not known. Known categories: "%s"',
                implode('", "', $queryCategoryNames), implode('", "', $categoryNames)
            ));
        }
    }
}
