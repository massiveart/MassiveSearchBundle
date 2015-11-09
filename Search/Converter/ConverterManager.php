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
     * @var ConverterInterface[][]
     */
    private $converter = [];

    /**
     * Add a converter to the manager.
     *
     * @param string $from source format.
     * @param string $to target format.
     * @param ConverterInterface $converter
     */
    public function addConverter($from, $to, ConverterInterface $converter)
    {
        if (!array_key_exists($from, $this->converter)) {
            $this->converter[$from] = [];
        }

        $this->converter[$from][$to] = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($value, $from, $to)
    {
        if (!$this->hasConverter($from, $to)) {
            throw new NoConverterFoundException($from, $to);
        }

        return $this->converter[$from][$to]->convert($value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasConverter($from, $to)
    {
        return array_key_exists($from, $this->converter) && array_key_exists($to, $this->converter[$from]);
    }
}
