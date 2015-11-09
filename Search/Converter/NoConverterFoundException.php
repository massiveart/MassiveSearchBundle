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
class NoConverterFoundException extends SearchException
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    public function __construct($from, $to)
    {
        parent::__construct(sprintf('No converter found to convert value from "%s" to "%s"', $from, $to));

        $this->from = $from;
        $this->to = $to;
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

    /**
     * Returns target format.
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }
}
