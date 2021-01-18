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
 * Converts objects/arrays into string using json_encode method.
 */
class JsonConverter implements ConverterInterface
{
    public function convert($value)
    {
        return \json_encode($value);
    }
}
