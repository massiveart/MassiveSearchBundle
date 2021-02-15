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

use Massive\Bundle\SearchBundle\Search\Document;

/**
 * Defines interface for value-converters.
 */
interface ConverterInterface
{
    /**
     * Converts value into indexable format.
     *
     * @param mixed $value
     * @param Document $document
     *
     * @return mixed
     */
    public function convert($value/*, Document $document = null*/);
}
