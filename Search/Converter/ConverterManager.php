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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements basic converter manager.
 */
class ConverterManager implements ConverterManagerInterface
{
    /**
     * @var string[]
     */
    private $converter = [];

    /**
     * @var ConverterInterface[]
     */
    private $serviceMap = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Add a converter to the manager.
     *
     * @param string $from source format.
     * @param string $converterId
     */
    public function addConverter($from, $converterId)
    {
        $this->converter[$from] = $converterId;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($value, $from)
    {
        if (!$this->hasConverter($from)) {
            throw new NoConverterFoundException($from);
        }

        return $this->getConverter($this->converter[$from])->convert($value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasConverter($from)
    {
        return array_key_exists($from, $this->converter);
    }

    /**
     * Returns converter for id.
     *
     * @param string $id service id.
     *
     * @return ConverterInterface
     */
    private function getConverter($id)
    {
        if (array_key_exists($id, $this->serviceMap)) {
            return $this->serviceMap[$id];
        }

        return $this->serviceMap[$id] = $this->container->get($id);
    }
}
