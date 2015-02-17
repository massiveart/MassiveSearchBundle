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

            $indexName = $indexMetadata->getIndexName();

            // if the index is locale aware, localize the index name
            if ($indexMetadata->getLocaleField()) {
                $indexName = $this->localizationStrategy->localizeIndexName($indexName);
            }

            $document = $this->converter->objectToDocument($indexMetadata, $object);
            $evaluator = $this->converter->getFieldEvaluator();

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
        if (!$query->getIndexes()) {
            $query->setIndexes($this->getIndexNames($query->getLocale()));
        }

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
        $this->adapter->purge($indexName);
    }

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
                $isLocalized = (boolean) $indexMetadata->getLocaleField();

                // handle localization
                if ($isLocalized && $locale) {
                    $indexName = $this->localizationStrategy->localizeIndexName($indexName, $locale);

                } elseif ($isLocalized) {

                    foreach ($this->getLocalizedIndexNamesFor($indexName) as $localizedIndexName) {
                        $indexNames[$indexName] = $localizedIndexName;
                    }
                    continue;
                }

                $indexNames[$indexName] = $indexName;
            }
        }

        return array_values($indexNames);
    }

    private function getLocalizedIndexNamesFor($indexName)
    {
        $adapterIndexNames = $this->adapter->listIndexes();

        $indexNames[] = $indexName;

        foreach ($adapterIndexNames as $adapterIndexName) {
            if ($this->localizationStrategy->isIndexVariantOf($indexName, $adapterIndexName)) {
                $indexNames[] = $adapterIndexName;
            }
        }

        return $indexNames;
    }
}
