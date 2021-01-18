<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

/**
 * Metadata for searchable objects.
 */
class IndexMetadata implements IndexMetadataInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var array
     */
    private $fieldMapping = [];

    /**
     * @var string
     */
    private $idField;

    /**
     * @var string
     */
    private $urlField;

    /**
     * @var string
     */
    private $titleField;

    /**
     * @var string
     */
    private $descriptionField;

    /**
     * @var string
     */
    private $imageUrlField;

    /**
     * @var string
     */
    private $localeField;

    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;
    }

    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    public function addFieldMapping($name, $mapping)
    {
        $this->fieldMapping[$name] = $mapping;
    }

    public function getIdField()
    {
        return $this->idField;
    }

    public function setIdField($idField)
    {
        $this->idField = $idField;
    }

    public function getUrlField()
    {
        return $this->urlField;
    }

    public function setUrlField($urlField)
    {
        $this->urlField = $urlField;
    }

    public function getTitleField()
    {
        return $this->titleField;
    }

    public function setTitleField($titleField)
    {
        $this->titleField = $titleField;
    }

    public function getDescriptionField()
    {
        return $this->descriptionField;
    }

    public function setDescriptionField($descriptionField)
    {
        $this->descriptionField = $descriptionField;
    }

    public function getImageUrlField()
    {
        return $this->imageUrlField;
    }

    public function setImageUrlField($imageUrlField)
    {
        $this->imageUrlField = $imageUrlField;
    }

    public function getLocaleField()
    {
        return $this->localeField;
    }

    public function setLocaleField($localeField)
    {
        $this->localeField = $localeField;
    }

    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;
    }
}
