<?php

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Representation of a indexed field
 * @package Massive\Bundle\SearchBundle\Search
 */
class Field
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $value;

    const TYPE_STRING = 'string';

    public static function create($name, $value, $type = self::TYPE_STRING)
    {
        $field = new Field();
        $field->setName($name);
        $field->setValue($value);
        $field->setType($type);

        return $field;
    }

    public function getName() 
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType() 
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getValue() 
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }
}
