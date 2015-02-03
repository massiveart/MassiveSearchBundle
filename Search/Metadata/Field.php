<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

/**
 * Simple value object for representing fields
 *
 * This is different from Property in that this represents
 * a value that should be determined from the "name" of the
 * mapped field instead of an explicit property declaration.
 *
 * For example:
 *
 *     <field name="foobar" /> <!-- yields a Field -->
 *     <field name="foobar" property="foo" /> <!-- yields a Property -->
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Return the name of the field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}

