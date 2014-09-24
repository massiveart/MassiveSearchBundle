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

    /**
     * Return the field name
     *
     * @return string
     */
    public function getName() 
    {
        return $this->name;
    }

    /**
     * Set the field name
     *
     * @param string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Return the field type
     *
     * @return string
     */
    public function getType() 
    {
        return $this->type;
    }

    /**
     * Set the field type
     * 
     * @param string
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Return the field value
     *
     * @return scalar
     */
    public function getValue() 
    {
        return $this->value;
    }

    /**
     * Set the field value
     *
     * @param scalar
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
