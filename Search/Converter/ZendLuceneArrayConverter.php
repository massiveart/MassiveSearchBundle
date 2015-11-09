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

/**
 * Converts array to string for zend lucene adapter.
 */
class ZendLuceneArrayConverter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($value)
    {
        return sprintf('|%s|', implode('|', $value));
    }
}
