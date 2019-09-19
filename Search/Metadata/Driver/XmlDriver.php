<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Driver;

use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Metadata\ClassMetadata;
use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\DriverInterface;
use Metadata\Driver\FileLocatorInterface;

/**
 * Loads MassiveSearch metadata from XML files.
 */
class XmlDriver extends AbstractFileDriver implements DriverInterface
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param FileLocatorInterface $locator
     * @param mixed $context Context name. e.g. backend, frontend
     */
    public function __construct(Factory $factory, FileLocatorInterface $locator)
    {
        parent::__construct($locator);
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'xml';
    }

    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, string $file): ?ClassMetadata
    {
        $classMetadata = $this->factory->createClassMetadata($class->name);

        $xml = simplexml_load_file($file);

        if (count($xml->children()) > 1) {
            throw new \InvalidArgumentException(sprintf(
                'Only one mapping allowed per class in file "%s',
                $file
            ));
        }

        if (0 == count($xml->children())) {
            throw new \InvalidArgumentException(sprintf('No mapping in file "%s"', $file));
        }

        $mapping = $xml->children()->mapping;

        $mappedClassName = (string) $mapping['class'];

        if ($class->getName() !== $mappedClassName) {
            throw new \InvalidArgumentException(sprintf(
                'Mapping in file "%s" does not correspond to class "%s", is a mapping for "%s"',
                $file,
                $class->getName(),
                $mappedClassName
            ));
        }

        $indexMapping = $this->getIndexMapping($mapping);
        $this->validateMapping($indexMapping, $file);

        // note that fields cannot be overridden in contexts
        $fields = $mapping->fields->children();
        $indexMapping['fields'] = [];
        foreach ($fields as $field) {
            $fieldName = (string) $field['name'];
            $fieldType = $field['type'];

            $indexMapping['fields'][$fieldName] = [
                'type' => (string) $fieldType,
                'field' => $this->getField($field, $fieldName),
            ];
        }

        if ($mapping->reindex) {
            if ($mapping->reindex['repository-method']) {
                $classMetadata->setReindexRepositoryMethod((string) $mapping->reindex['repository-method']);
            }
        }

        $indexMappings = array_merge(
            [
                '_default' => $indexMapping,
            ],
            $this->extractContextMappings($mapping, $indexMapping)
        );

        foreach ($indexMappings as $contextName => $mapping) {
            $indexMetadata = $this->factory->createIndexMetadata();
            $indexMetadata->setIndexName($mapping['index']);
            $indexMetadata->setIdField($mapping['id']);
            $indexMetadata->setLocaleField($mapping['locale']);
            $indexMetadata->setTitleField($mapping['title']);
            $indexMetadata->setUrlField($mapping['url']);
            $indexMetadata->setDescriptionField($mapping['description']);
            $indexMetadata->setImageUrlField($mapping['image']);

            foreach ($mapping['fields'] as $fieldName => $fieldData) {
                $indexMetadata->addFieldMapping($fieldName, $fieldData);
            }

            $classMetadata->addIndexMetadata($contextName, $indexMetadata);
        }

        return $classMetadata;
    }

    private function getIndexMapping(\SimpleXmlElement $mapping)
    {
        $indexMapping = [];

        $indexField = $this->getMapping($mapping, 'index');
        $indexMapping['index'] = $indexField;

        $idField = $this->getMapping($mapping, 'id');
        $indexMapping['id'] = $idField;

        $localeField = $this->getMapping($mapping, 'locale');
        $indexMapping['locale'] = $localeField;

        $titleField = $this->getMapping($mapping, 'title');
        $indexMapping['title'] = $titleField;

        $urlField = $this->getMapping($mapping, 'url');
        $indexMapping['url'] = $urlField;

        $descriptionField = $this->getMapping($mapping, 'description');
        $indexMapping['description'] = $descriptionField;

        $imageField = $this->getMapping($mapping, 'image');
        $indexMapping['image'] = $imageField;

        return $indexMapping;
    }

    private function validateMapping($indexMapping, $file)
    {
        foreach (['index', 'id', 'title'] as $required) {
            if (!$indexMapping[$required]) {
                throw new \InvalidArgumentException(sprintf(
                    'Required field for mapping is not present "%s" in "%s"',
                    $required,
                    $file
                ));
            }
        }
    }

    /**
     * Return the value object for the mapping.
     *
     * @param \SimpleXmlElement $mapping
     * @param mixed $field
     */
    private function getMapping(\SimpleXmlElement $mapping, $field)
    {
        $field = $mapping->$field;

        if (!$field->getName()) {
            return;
        }

        return $this->getField($field);
    }

    private function getField(\SimpleXmlElement $field)
    {
        if (isset($field['expr']) && isset($field['property'])) {
            throw new \InvalidArgumentException(sprintf(
                '"expr" and "property" attributes are mutually exclusive in mapping for "%s"',
                ($field)
            ));
        }

        // if not property or expression given, try using the "name"
        if (isset($field['name']) && !isset($field['expr']) && !isset($field['property'])) {
            return $this->factory->createMetadataField((string) $field['name']);
        }

        if (isset($field['expr'])) {
            return $this->factory->createMetadataExpression((string) $field['expr']);
        }

        if (isset($field['property'])) {
            return $this->factory->createMetadataProperty((string) $field['property']);
        }

        if (isset($field['value'])) {
            return $this->factory->createMetadataValue((string) $field['value']);
        }

        throw new \InvalidArgumentException(sprintf(
            'Mapping for "%s" must have one of either the "expr" or "property" attributes.',
            $field
        ));
    }

    /**
     * Overwrite the default mapping if there exists a <context> section
     * which matches the context given in the constructor of this class.
     *
     * @param \SimpleXmlElement $mapping
     */
    private function extractContextMappings(\SimpleXmlElement $mapping, $indexMapping)
    {
        $contextMappings = [];
        foreach ($mapping->context as $context) {
            if (!isset($context['name'])) {
                throw new \InvalidArgumentException(sprintf(
                    'No name given to context in XML mapping file for "%s"',
                    $mapping['class']
                ));
            }

            $contextName = (string) $context['name'];

            $contextMapping = $this->getIndexMapping($context);
            $contextMapping = array_filter($contextMapping, function ($value) {
                if (null === $value) {
                    return false;
                }

                return true;
            });
            $contextMappings[$contextName] = array_merge(
                $indexMapping,
                $contextMapping
            );
        }

        return $contextMappings;
    }
}
