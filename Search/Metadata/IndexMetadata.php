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

    /**
     * @var string
     */
    private $categoryName;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * {@inheritdoc}
     */
    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldMapping($name, $mapping)
    {
        $this->fieldMapping[$name] = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdField()
    {
        return $this->idField;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdField($idField)
    {
        $this->idField = $idField;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlField()
    {
        return $this->urlField;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlField($urlField)
    {
        $this->urlField = $urlField;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitleField()
    {
        return $this->titleField;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitleField($titleField)
    {
        $this->titleField = $titleField;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescriptionField()
    {
        return $this->descriptionField;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescriptionField($descriptionField)
    {
        $this->descriptionField = $descriptionField;
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrlField()
    {
        return $this->imageUrlField;
    }

    /**
     * {@inheritdoc}
     */
    public function setImageUrlField($imageUrlField)
    {
        $this->imageUrlField = $imageUrlField;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleField()
    {
        return $this->localeField;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocaleField($localeField)
    {
        $this->localeField = $localeField;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryName($category)
    {
        $this->categoryName = $category;
    }
}
