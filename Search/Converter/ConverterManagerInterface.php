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
 * Interface for converter manager.
 */
interface ConverterManagerInterface
{
    /**
     * Converts value from a source format to a target format.s.
     *
     * @param mixed $value
     * @param string $from source format.
     * @param string $to target format.
     *
     * @return mixed
     */
    public function convert($value, $from, $to);

    /**
     * Returns true if a converter exists which converts from source to target format.
     *
     * @param string $from source format.
     * @param string $to target format.
     *
     * @return bool
     */
    public function hasConverter($from, $to);
}
