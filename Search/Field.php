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

    /**
     * @var string
     */
    protected $indexStrategy;

    /**
     * Store the field as a string
     */
    const TYPE_STRING = 'string';

    /**
     * Aggregate the fields in a single aggregate field for indexing and
     * store the fields
     */
    const INDEX_AGGREGATE = 'aggregate';

    /**
     * Index, but do not sture the field
     */
    const INDEX_UNSTORED = 'unstored';

    /**
     * Indexed and stored
     */
    const INDEX_STORED_INDEXED = 'stored_indexed';

    public static function getValidTypes()
    {
        return array(
            self::TYPE_STRING,
        );
    }

    public function __construct($name, $value, $type = self::TYPE_STRING, $indexStrategy = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
        $this->indexStrategy = $indexStrategy;
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

    /**
     * Get the index strategy
     *
     * @return string One of Field::INDEX_*
     */
    public function getIndexStrategy() 
    {
        return $this->indexStrategy;
    }

    /**
     * Set the index strategy
     *
     * @param string $indexStrategy One of Field::INDEX_*
     */
    public function setIndexStrategy($indexStrategy)
    {
        $this->indexStrategy = $indexStrategy;
    }
}
