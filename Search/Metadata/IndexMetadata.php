<?php

namespace Massive\Bundle\SearchBundle\Search\Metadata;

use Metadata\ClassMetadata;

/**
 * Metadata for searchable objects
 */
class IndexMetadata extends ClassMetadata implements IndexMetadataInterface
{
    protected $indexName;
    protected $fieldMapping = array();
    protected $idField;
    protected $urlField;
    protected $titleField;
    protected $descriptionField;
    protected $imageUrlField;

    public function getName()
    {
        return $this->name;
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
}
