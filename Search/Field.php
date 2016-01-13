<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Representation of a indexed field.
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
     * The value should be stored (i.e. it should be retrievable).
     *
     * @var bool
     */
    protected $stored;

    /**
     * The value should be indexed (i.e. it should be searchable).
     *
     * @var bool
     */
    protected $indexed;

    /**
     * Aggregate the values with other aggregate values into an indexed
     * aggregate field (this can have performance benefits on certain
     * implementations, like Zend Lucene, as it reduces the number of indexed
     * fields on the document).
     *
     * Note for this to be beneficial the field should NOT be indexed (as the
     * field value will be tokenized and indexed in the aggregate field).
     *
     * @var bool
     */
    protected $aggregate;

    /**
     * Store the field as a string.
     */
    const TYPE_STRING = 'string';

    /**
     * Store the field as a array.
     */
    const TYPE_ARRAY = 'array';

    /**
     * Null value should be ignored by adapter.
     */
    const TYPE_NULL = 'null';

    public static function getValidTypes()
    {
        return [
            self::TYPE_STRING,
            self::TYPE_ARRAY,
            self::TYPE_NULL,
        ];
    }

    public function __construct(
        $name,
        $value,
        $type = self::TYPE_STRING,
        $stored = true,
        $indexed = true,
        $aggregate = false
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
        $this->stored = $stored;
        $this->indexed = $indexed;
        $this->aggregate = $aggregate;
    }

    /**
     * Return the field name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the field name.
     *
     * @param string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Return the field type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the field type.
     *
     * @param string
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Return the field value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the field value.
     *
     * @param scalar
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Set if the field should be stored or not
     * Stored field values are retrievable but not necessarily
     * indexed.
     *
     * @param bool $stored
     */
    public function setStored($stored)
    {
        $this->stored = $stored;
    }

    /**
     * Return true if the field should be stored.
     *
     * @return bool
     */
    public function isStored()
    {
        return $this->stored;
    }

    /**
     * Set if the field should be indexed.
     *
     * @return bool
     */
    public function isIndexed()
    {
        return $this->indexed;
    }

    /**
     * Aggregate the values of this field into a single indexed field.
     *
     * @param bool $aggregate
     */
    public function setAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * Return true if the field values should be in an aggregate indexed field.
     *
     * @return bool
     */
    public function isAggregate()
    {
        return $this->aggregate;
    }
}
