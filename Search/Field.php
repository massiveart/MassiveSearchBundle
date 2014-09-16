<?php

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Representation of a indexed field
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
    const TYPE_BINRARY = 'binary';

    public static function getValidTypes()
    {
        return array(
            self::TYPE_STRING,
            self::TYPE_BINARY,
        );
    }

    public function __construct($name, $value, $type = self::TYPE_STRING)
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
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
