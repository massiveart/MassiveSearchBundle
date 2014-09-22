<?php

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Event\SearchEvent;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Metadata\MetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Massive\Bundle\SearchBundle\Search\SearchQueryBuilder;

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
     * @var Factory
     */
    protected $factory;

    public function __construct(
        Factory $factory,
        AdapterInterface $adapter,
        MetadataFactory $metadataFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->factory = $factory;
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
            throw new \RuntimeException(
                sprintf(
                    'There is no search mapping for class "%s"',
                    $objectClass
                )
            );
        }

        return $metadata->getOutsideClassMetadata();
    }

    public function deindex($object)
    {
        $metadata = $this->getMetadata($object);
        $indexName = $metadata->getIndexName();
        $document = $this->objectToDocument($metadata, $object);

        $this->adapter->deindex($document, $indexName);
    }

    /**
     * {@inheritdoc}
     */
    public function index($object)
    {
        $metadata = $this->getMetadata($object);
        $indexName = $metadata->getIndexName();
        $document = $this->objectToDocument($metadata, $object);

        $this->eventDispatcher->dispatch(
            SearchEvents::PRE_INDEX,
            new PreIndexEvent($object, $document, $metadata, $indexName)
        );

        $this->adapter->index($document, $indexName);
    }

    /**
     * Create a new search builder
     *
     * @return SearchQueryBuilder
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

    private function objectToDocument($metadata, $object)
    {
        $idField = $metadata->getIdField();
        $urlField = $metadata->getUrlField();
        $titleField = $metadata->getTitleField();
        $descriptionField = $metadata->getDescriptionField();
        $imageUrlField = $metadata->getImageUrlField();
        $localeField = $metadata->getLocaleField();
        $fields = $metadata->getFieldMapping();

        $accessor = PropertyAccess::createPropertyAccessor();

        $document = $this->factory->makeDocument();
        $document->setId($accessor->getValue($object, $idField));
        $document->setClass($metadata->getName());

        if ($urlField) {
            $url = $accessor->getValue($object, $urlField);
            if ($url) {
                $document->setUrl($accessor->getValue($object, $urlField));
            }
        }

        if ($titleField) {
            $title = $accessor->getValue($object, $titleField);
            if ($title) {
                $document->setTitle($accessor->getValue($object, $titleField));
            }
        }

        if ($descriptionField) {
            $description = $accessor->getValue($object, $descriptionField);
            if ($description) {
                $document->setDescription($accessor->getValue($object, $descriptionField));
            }
        }

        if ($imageUrlField) {
            $imageUrl = $accessor->getValue($object, $imageUrlField);
            $document->setImageUrl($imageUrl);
        }

        if ($localeField) {
            $locale = $accessor->getValue($object, $localeField);
            $document->setLocale($locale);
        }

        foreach ($fields as $fieldName => $fieldMapping) {
            $document->addField(
                $this->factory->makeField($fieldName, $accessor->getValue($object, $fieldName), $fieldMapping['type'])
            );
        }

        return $document;
    }
}
