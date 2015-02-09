<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\Driver\AbstractFileDriver;
use Massive\Bundle\SearchBundle\Search\Factory;
use Metadata\Driver\FileLocatorInterface;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;

class XmlDriver extends AbstractFileDriver implements DriverInterface
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var string
     */
    private $context;

    /**
     * @param Factory $factory
     * @param FileLocatorInterface $locator
     * @param mixed $context Context name. e.g. backend, frontend
     */
    public function __construct(Factory $factory, FileLocatorInterface $locator, $context = null)
    {
        parent::__construct($locator);
        $this->factory = $factory;
        $this->context = $context;
    }


    /**
     * {@inheritDoc}
     */
    public function getExtension()
    {
        return 'xml';
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $meta = $this->factory->makeIndexMetadata($class->name);
        $xml = simplexml_load_file($file);

        if (count($xml->children()) > 1) {
            throw new \InvalidArgumentException(sprintf(
                'Only one mapping allowed per class in file "%s',
                $file
            ));
        }

        if (count($xml->children()) == 0) {
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

        $this->applyContextMapping($mapping);

        $indexName = (string) $mapping->index['name'];
        $meta->setIndexName($indexName);

        $idField = $this->getMapping($mapping, 'id');
        $meta->setIdField($idField);

        $localeField = (string) $mapping->localeField['name'];
        $localeField = $this->getMapping($mapping, 'locale', false);
        $meta->setLocaleField($localeField);

        $titleField = $this->getMapping($mapping, 'title');
        $meta->setTitleField($titleField);

        $urlField = $this->getMapping($mapping, 'url');
        $meta->setUrlField($urlField);

        $descriptionField = $this->getMapping($mapping, 'description', false);
        $meta->setDescriptionField($descriptionField);


        $fields = $mapping->fields->children();
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];

            $meta->addFieldMapping((string) $fieldName, array(
                'type' => (string) $fieldType,
                'field' => $this->getField($field, $fieldName)
            ));
        }

        return $meta;
    }

    /**
     * Return the value object for the mapping
     *
     * @param \SimpleXmlElement $mapping
     * @param mixed $field
     */
    private function getMapping(\SimpleXmlElement $mapping, $field, $required = true)
    {
        if (!isset($mapping->$field)) {
            if ($required) {
                throw new \InvalidArgumentException(sprintf(
                    'Mapping for class "%s" does not have field "%s"',
                    $mapping['class'],
                    $field
                ));
            }

            return null;
        }

        $field = $mapping->$field;

        return $this->getField($field);
    }

    private function getField(\SimpleXmlElement $field)
    {
        if (isset($field['expr']) && isset($field['property'])) {
            throw new \InvalidArgumentException(sprintf(
                '"expr" and "proprty" attributes are mutually exclusive in mapping for "%s"',
                $field
            ));
        }

        // if not property or expression given, try using the "name"
        if (isset($field['name']) && !isset($field['expr']) && !isset($field['property'])) {
            return $this->factory->makeMetadataField((string) $field['name']);
        }

        if (isset($field['expr'])) {
            return $this->factory->makeMetadataExpression((string) $field['expr']);
        }

        if (isset($field['property'])) {
            return $this->factory->makeMetadataProperty((string) $field['property']);
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
    private function applyContextMapping(\SimpleXmlElement $mapping)
    {
        foreach ($mapping->context as $context) {
            if (!isset($context['name'])) {
                throw new \InvalidArgumentException(sprintf(
                    'No name given to context in XML mapping file for "%s"',
                    $mapping['class']
                ));
            }

            if ((string) $context['name'] != $this->context) {
                continue;
            }

            foreach ($context as $name => $element) {
                if (isset($mapping->$name)) {
                    unset($mapping->$name);
                }
                $newElement = $mapping->addChild($name);

                foreach ($element->attributes() as $attrName => $attrValue) {
                    $newElement[$attrName] = $attrValue;
                }
            }
        }
    }
}
