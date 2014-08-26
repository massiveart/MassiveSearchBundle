<?php

namespace Massive\Bundle\SearchBundle\Search;

class Document
{
    protected $fields = array();

    protected $id;
    protected $class;
    protected $title;
    protected $description;
    protected $url;

    public function getId() 
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function addField(Field $field)
    {
        $this->fields[] = $field;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getUrl() 
    {
        return $this->url;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getClass() 
    {
        return $this->class;
    }
    
    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getTitle() 
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription() 
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
}
