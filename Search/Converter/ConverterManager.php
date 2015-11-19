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
 * Implements basic converter manager.
 */
class ConverterManager implements ConverterManagerInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converter = [];

    /**
     * Add a converter to the manager.
     *
     * @param string $from source format.
     * @param ConverterInterface $converter
     */
    public function addConverter($from, ConverterInterface $converter)
    {
        $this->converter[$from] = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($value, $from)
    {
        if (!$this->hasConverter($from)) {
            throw new ConverterNotFoundException($from);
        }

        return $this->converter[$from]->convert($value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasConverter($from)
    {
        return array_key_exists($from, $this->converter);
    }
}
