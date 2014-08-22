<?php

namespace Massive\Bundle\SearchBundle\Search\Metadata;

use Metadata\ClassMetadata;

/**
 * Metadata for searchable objects
 *
 * @author Daniel Leech <daniel@massiveart.com>
 */
class IndexMetadata extends ClassMetadata
{
    protected $indexName;
    protected $fieldMapping = array();
    protected $idField;

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
}
