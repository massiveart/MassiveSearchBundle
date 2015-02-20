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
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Metadata\MetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;
use Symfony\Component\PropertyAccess\PropertyAccessor;
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

        $objectClass = get_class($object);
        $metadata = $this->metadataFactory->getMetadataForClass($objectClass);

        if (null === $metadata) {
            throw new MetadataNotFoundException(
                sprintf(
                    'There is no search mapping for class "%s"',
                    $objectClass
                )
            );
        }

        return $metadata->getOutsideClassMetadata();
    }

    /**
     * {@inheritDoc}
     */
    public function deindex($object)
    {
        $metadata = $this->getMetadata($object);

        foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
            $this->indexesToFlush[$indexMetadata->getIndexName()] = true;
            $indexNames = $this->getExpandedIndexNamesFor($indexMetadata->getIndexName());

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
            $this->indexesToFlush[$indexMetadata->getIndexName()] = true;

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
        $this->expandQueryIndexes($query);

        $this->eventDispatcher->dispatch(
            SearchEvents::SEARCH,
            new SearchEvent($query)
        );

        $hits = $this->adapter->search($query);

        $reflections = array();

        /** @var QueryHit $hit */
        foreach ($hits as $hit) {
            $document = $hit->getDocument();

            // only throw events for existing documents
            if (!class_exists($document->getClass())) {
                continue;
            }

            // we need a reflection instance of the document in event listeners
            if (!isset($reflections[$document->getClass()])) {
                $reflections[$document->getClass()] = new \ReflectionClass($document->getClass());
            }

            $this->eventDispatcher->dispatch(
                SearchEvents::HIT,
                new HitEvent($hit, $reflections[$document->getClass()])
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
        $this->indexesToFlush[$indexName] = true;
        $indexes = $this->getExpandedIndexNamesFor($indexName);
        foreach ($indexes as $indexName) {
            $this->adapter->purge($indexName);
        }
    }

    /**
     * List all of the expanded index names in the search implementation
     * optionally only in the given locale.
     *
     * @param string $locale
     */
    public function getIndexNames($locale = null)
    {
        $classNames = $this->metadataFactory->getAllClassNames();
        $indexNames = array();

        foreach ($classNames as $className) {
            $metadata = $this->metadataFactory->getMetadataForClass($className);

            if (null === $metadata) {
                throw new \InvalidArgumentException(sprintf(
                    'Cannot get metadata for class "%s"', $className
                ));
            }
            
            $metadata = $metadata->getOutsideClassMetadata();

            foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
                $indexName = $indexMetadata->getIndexName();

                foreach ($this->getExpandedIndexNamesFor($indexName, $locale) as $localizedIndexName) {
                    $indexNames[$localizedIndexName] = $localizedIndexName;
                }
            }
        }

        return array_values($indexNames);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->adapter->flush(array_keys($this->indexesToFlush));
        $this->indexesToFlush = array();
    }

    private function getExpandedIndexNamesFor($indexName, $locale = null)
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

    private function expandQueryIndexes(SearchQuery $query)
    {
        if (!$query->getIndexes()) {
            $indexNames = $this->getIndexNames($query->getLocale());
            $query->setIndexes($indexNames);
            return;
        }

        $expandedIndexes = array();

        foreach ($query->getIndexes() as $index) {
            foreach ($this->getExpandedIndexNamesFor($index) as $expandedIndex) {
                $expandedIndexes[$expandedIndex] = $expandedIndex;
            }
        }

        $query->setIndexes($expandedIndexes);

    }
}
