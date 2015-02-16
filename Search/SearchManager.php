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

    public function __construct(
        AdapterInterface $adapter,
        MetadataFactory $metadataFactory,
        ObjectToDocumentConverter $converter,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->converter = $converter;
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

            die('asd');
        foreach ($metadata->getIndexMetadatas() as $indexMetadata) {
            $indexName = $indexMetadata->getIndexName();
            $document = $this->converter->objectToDocument($indexMetadata, $object);
        }

        $this->adapter->deindex($document, $indexName);
    }

    /**
     * {@inheritdoc}
     */
    public function index($object)
    {
        $indexMetadatas = $this->getMetadata($object);

        foreach ($indexMetadatas->getIndexMetadatas() as $indexMetadata) {
            $indexName = $indexMetadata->getIndexName();
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
        $indexNames = $query->getIndexes();

        if (null === $indexNames) {
            throw new \Exception('Searching all indexes is not yet implemented');
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
            if (!isset($metas[$document->getClass()])) {
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
}
