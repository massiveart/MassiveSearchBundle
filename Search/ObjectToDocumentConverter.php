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
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;

/**
 * Convert mapped objects to search documents.
 */
class ObjectToDocumentConverter
{
    /**
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var ConverterManagerInterface
     */
    private $converterManager;

    /**
     * @var string[]
     */
    private $blockValues = [];

    public function __construct(
        Factory $factory,
        FieldEvaluator $fieldEvaluator,
        ConverterManagerInterface $converterManager
    ) {
        $this->factory = $factory;
        $this->fieldEvaluator = $fieldEvaluator;
        $this->converterManager = $converterManager;
    }

    /**
     * Return the field evaluator.
     *
     * @return FieldEvaluator
     */
    public function getFieldEvaluator()
    {
        return $this->fieldEvaluator;
    }

    /**
     * Map the given object to a new document using the
     * given metadata.
     *
     * @param object $object
     *
     * @return Document
     */
    public function objectToDocument(IndexMetadata $metadata, $object)
    {
        $indexNameField = $metadata->getIndexName();
        $idField = $metadata->getIdField();
        $urlField = $metadata->getUrlField();
        $titleField = $metadata->getTitleField();
        $descriptionField = $metadata->getDescriptionField();
        $imageUrlField = $metadata->getImageUrlField();
        $localeField = $metadata->getLocaleField();
        $fieldMapping = $metadata->getFieldMapping();

        $document = $this->factory->createDocument();
        $document->setIndex($this->fieldEvaluator->getValue($object, $indexNameField));
        $document->setId($this->fieldEvaluator->getValue($object, $idField));
        $document->setClass($metadata->getName());

        if ($urlField) {
            $url = $this->fieldEvaluator->getValue($object, $urlField);
            if ($url) {
                $document->setUrl($url);
            }
        }

        if ($titleField) {
            $title = $this->fieldEvaluator->getValue($object, $titleField);
            if ($title) {
                $document->setTitle($title);
            }
        }

        if ($descriptionField) {
            $description = $this->fieldEvaluator->getValue($object, $descriptionField);
            if ($description) {
                $document->setDescription($description);
            }
        }

        if ($imageUrlField) {
            $imageUrl = $this->fieldEvaluator->getValue($object, $imageUrlField);
            $document->setImageUrl($imageUrl);
        }

        if ($localeField) {
            $locale = $this->fieldEvaluator->getValue($object, $localeField);
            $document->setLocale($locale);
        }

        $this->blockValues = [];
        $this->populateDocument($document, $object, $fieldMapping);

        // Adds the merged data of each content-block (even nested-blocks) to the document.
        if (0 < \count($this->blockValues)) {
            $mapping = $this->addMappingOptions();
            $blockValues = \implode(' ', $this->blockValues);
            $this->addDocumentField($document, 'contentBlocks', $blockValues, $mapping, Field::TYPE_STRING);
        }

        return $document;
    }

    /**
     * Populate the Document with the actual values from the object which
     * is being indexed.
     *
     * @param Document $document
     * @param mixed $object
     * @param array $fieldMapping
     * @param bool $isBlockScope
     *
     * @throws \InvalidArgumentException
     */
    private function populateDocument($document, $object, $fieldMapping, $isBlockScope = false)
    {
        foreach ($fieldMapping as $fieldName => $mapping) {
            $this->hasRequiredMapping($document, $mapping);
            $mapping = $this->addMappingOptions($mapping);

            if ('complex' == $mapping['type']) {
                if (!isset($mapping['mapping'])) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            '"complex" field mappings must have an additional array key "mapping" ' .
                            'which contains the mapping for the complex structure in mapping: %s',
                            \print_r($mapping, true)
                        )
                    );
                }

                $childObjects = $this->fieldEvaluator->getValue($object, $mapping['field']);

                if (null === $childObjects) {
                    continue;
                }

                foreach ($childObjects as $i => $childObject) {
                    $this->populateDocument(
                        $document,
                        $childObject,
                        $mapping['mapping']->getFieldMapping(),
                        true
                    );
                }

                continue;
            }

            $type = $mapping['type'];
            $value = $this->fieldEvaluator->getValue($object, $mapping['field']);

            if (Field::TYPE_STRING !== $type && Field::TYPE_ARRAY !== $type) {
                $value = $this->converterManager->convert($value, $type, $document);
                $type = $this->getValueType($value);
            }

            if (null !== $value && false === \is_scalar($value) && false === \is_array($value)) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Search field "%s" resolved to not supported type "%s". Only scalar (single) or array values can be indexed.',
                        $fieldName,
                        \gettype($value)
                    )
                );
            }

            if (\is_array($value) && (isset($value['value']) || isset($value['fields']))) {
                if (isset($value['value'])) {
                    $valueType = $this->getValueType($value['value']);
                    $this->addDocumentField($document, $fieldName, $value['value'], $mapping, $valueType);
                }

                if (isset($value['fields'])) {
                    /** @var Field $field */
                    foreach ($value['fields'] as $field) {
                        $field = clone $field;
                        $field->setName($fieldName . '#' . $field->getName());
                        $document->addField($field);
                    }
                }

                continue;
            }

            if ('complex' !== $mapping['type']) {
                if ($isBlockScope && $value && Field::TYPE_STRING === $type) {
                    $this->blockValues[] = \strip_tags($value);
                } elseif (!$isBlockScope) {
                    $this->addDocumentField($document, $fieldName, $value, $mapping, $type);
                }

                continue;
            }

            foreach ($value as $key => $itemValue) {
                $this->addDocumentField($document, $fieldName . $key, $itemValue, $mapping);
            }
        }
    }

    /**
     * Adds some default mapping options to the given array.
     */
    private function addMappingOptions($mapping = []): array
    {
        return \array_merge(
            [
                'stored' => true,
                'aggregate' => false,
                'indexed' => true,
            ],
            $mapping
        );
    }

    /**
     * Adds a search field to the Document.
     *
     * @param Document $document
     * @param string $fieldName
     * @param mixed $value
     * @param array $mapping
     * @param string|null $type
     */
    private function addDocumentField($document, $fieldName, $value, $mapping, $type = null): void
    {
        if (null === $type && \array_key_exists('type', $mapping)) {
            $type = $mapping['type'];
        }

        $document->addField(
            $this->factory->createField(
                $fieldName,
                $value,
                $type ?: Field::TYPE_STRING,
                $mapping['stored'],
                $mapping['indexed'],
                $mapping['aggregate']
            )
        );
    }

    /**
     * Checks if all mandatory options are available in the given mapping.
     *
     * @param Document $document
     * @param array $mapping
     */
    private function hasRequiredMapping($document, $mapping): void
    {
        $requiredMappings = ['field', 'type'];

        foreach ($requiredMappings as $requiredMapping) {
            if (!isset($mapping[$requiredMapping])) {
                throw new \RuntimeException(
                    \sprintf(
                        'Mapping for "%s" does not have "%s" key',
                        \get_class($document),
                        $requiredMapping
                    )
                );
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function getValueType($value): string
    {
        if (\is_null($value)) {
            return Field::TYPE_NULL;
        }

        if (\is_array($value)) {
            return Field::TYPE_ARRAY;
        }

        return Field::TYPE_STRING;
    }
}
