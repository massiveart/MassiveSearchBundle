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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\Expression;

/**
 * Convert mapped objects to search documents
 */
class ObjectToDocumentConverter
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var PropertyAccess
     */
    private $accessor;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(Factory $factory, ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->factory = $factory;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Map the given object to a new document using the
     * given metadata.
     *
     * @param IndexMetadata $metadata
     * @param object $object
     * @return Document
     */
    public function objectToDocument(IndexMetadata $metadata, $object)
    {
        $idField = $metadata->getIdField();
        $urlField = $metadata->getUrlField();
        $titleField = $metadata->getTitleField();
        $descriptionField = $metadata->getDescriptionField();
        $imageUrlField = $metadata->getImageUrlField();
        $localeField = $metadata->getLocaleField();
        $fieldMapping = $metadata->getFieldMapping();

        $document = $this->factory->makeDocument();
        $document->setId($this->getValue($object, $idField));
        $document->setClass($metadata->getName());

        if ($urlField) {
            $url = $this->getValue($object, $urlField);
            if ($url) {
                $document->setUrl($url);
            }
        }

        if ($titleField) {
            $title = $this->getValue($object, $titleField);
            if ($title) {
                $document->setTitle($title);
            }
        }

        if ($descriptionField) {
            $description = $this->getValue($object, $descriptionField);
            if ($description) {
                $document->setDescription($description);
            }
        }

        if ($imageUrlField) {
            $imageUrl = $this->getValue($object, $imageUrlField);
            $document->setImageUrl($imageUrl);
        }

        if ($localeField) {
            $locale = $this->getValue($object, $localeField);
            $document->setLocale($locale);
        }

        $this->populateDocument($document, $object, $fieldMapping);

        return $document;
    }

    /**
     * Evaluate the value from the given object and field
     *
     * @param mixed $object
     * @param mixed $field
     */
    private function getValue($object, $field)
    {
        switch (get_class($field)) {
            case 'Massive\Bundle\SearchBundle\Search\Metadata\Property':
                return $this->getPropertyValue($object, $field);
            case 'Massive\Bundle\SearchBundle\Search\Metadata\Expression':
                return $this->getExpressionValue($object, $field);
            case 'Massive\Bundle\SearchBundle\Search\Metadata\Field':
                return $this->getFieldValue($object, $field);
        }

        throw new \RuntimeException(sprintf(
            'Expected either a Property or an Expression when converting object of class "%s" to document',
            get_class($object)
        ));
    }

    /**
     * Evaluate a property (using PropertyAccess)
     *
     * @param mixed $object
     * @param Property $field
     */
    private function getPropertyValue($object, Property $field)
    {
        return $this->accessor->getValue($object, $field->getProperty());
    }

    /**
     * Return a value determined from the name of the field
     * rather than an explicit property.
     *
     * If the object is an array, then force the array syntax.
     *
     * @param mixed $object
     * @param Field $field
     */
    private function getFieldValue($object, Field $field)
    {
        if (is_array($object)) {
            $path = '[' . $field->getName(). ']';
        } else {
            $path = $field->getName();
        }

        return $this->accessor->getValue($object, $path);
    }


    /**
     * Evaluate an expression (ExpressionLanguage)
     *
     * @param mixed $object
     * @param Expression $field
     */
    private function getExpressionValue($object, Expression $field)
    {
        return $this->expressionLanguage->evaluate($field->getExpression(), array(
            'object' => $object
        ));
    }

    /**
     * Populate the Document with the actual values from the object which
     * is being indexed.
     *
     * @param Document $document
     * @param mixed $object
     * @param array $fieldMapping
     * @param string $prefix Prefix the document field name (used when called recursively)
     * @throws \InvalidArgumentException
     */
    private function populateDocument($document, $object, $fieldMapping, $prefix = '')
    {
        foreach ($fieldMapping as $fieldName => $mapping) {
            if ($mapping['type'] == 'complex') {

                if (!isset($mapping['mapping'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '"complex" field mappings must have an additional array key "mapping" ' .
                            'which contains the mapping for the complex structure in mapping: %s',
                            print_r($mapping, true)
                        )
                    );
                }

                $childObjects = $this->accessor->getValue($object, $fieldName);

                foreach ($childObjects as $i => $childObject) {
                    $this->populateDocument(
                        $document,
                        $childObject,
                        $mapping['mapping']->getFieldMapping(),
                        $prefix . $fieldName . $i
                    );
                }

                continue;
            }

            $value = $this->getValue($object, $mapping['field']);

            if (!is_array($value)) {
                $document->addField(
                    $this->factory->makeField(
                        $prefix . $fieldName,
                        $value,
                        $mapping['type']
                    )
                );

                continue;
            }

            foreach ($value as $key => $itemValue) {
                $document->addField(
                    $this->factory->makeField(
                        $prefix . $fieldName . $key,
                        $itemValue,
                        $mapping['type']
                    )
                );
            }
        }
    }
}
