<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Converter\Types;

use Massive\Bundle\SearchBundle\Search\Converter\ConverterInterface;

/**
 * Converts DateTime into string using format method
 */
class DateConverter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($value)
    {
        return is_null($value) ? '' : $value->format(DATE_RFC3339);
    }
}
