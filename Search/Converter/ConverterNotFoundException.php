<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Converter;

use Massive\Bundle\SearchBundle\Search\Exception\SearchException;

/**
 * Indicates missing converter.
 */
class ConverterNotFoundException extends SearchException
{
    /**
     * @var string
     */
    private $from;

    public function __construct($from)
    {
        parent::__construct(sprintf('No converter found to convert value from type "%s"', $from));

        $this->from = $from;
    }

    /**
     * Returns source format.
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }
}
