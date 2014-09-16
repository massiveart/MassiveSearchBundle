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

    /**
     * {@inheritdoc}
     */
    public function index($object)
    {
        $metadata = $this->getMetadata($object);

        $this->indexWithMetadata($object, $metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function indexWithMetadata($object, IndexMetadataInterface $metadata)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $indexName = $metadata->getIndexName();

        $idField = $metadata->getIdField();
        $urlField = $metadata->getUrlField();
        $titleField = $metadata->getTitleField();
        $descriptionField = $metadata->getDescriptionField();

        $fields = $metadata->getFieldMapping();

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

        foreach ($fields as $fieldName => $fieldMapping) {
            $document->addField(
                $this->factory->makeField($fieldName, $accessor->getValue($object, $fieldName), $fieldMapping['type'])
            );
        }

        $this->adapter->index($document, $indexName);
    }

    /**
     * {@inheritdoc}
     */
    public function search($string, $indexNames = null)
    {
        if (null === $indexNames) {
            throw new \Exception('Not implemented yet');
        }

        $this->eventDispatcher->dispatch(
            SearchEvents::SEARCH,
            new SearchEvent($string, $indexNames)
        );

        $indexNames = (array)$indexNames;

        $hits = $this->adapter->search($string, $indexNames);

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
}
