<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\ReIndex;

/**
 * Registry for reindex providers.
 */
class ReIndexProviderRegistry
{
    /**
     * @var ReIndexProviderInterface
     */
    private $providers = [];

    /**
     * Add a reindex provider to the registry.
     *
     * @param string $name
     * @param ReIndexProviderInterface $provider
     *
     * @throws \InvalidArgumentException
     */
    public function addProvider($name, ReIndexProviderInterface $provider)
    {
        if (isset($this->providers[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'ReIndex provider with name "%s" has already been registered.',
                $name
            ));
        }

        $this->providers[$name] = $provider;
    }

    /**
     * Return the registered providers.
     *
     * @return ReIndexProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Return a specific provider.
     */
    public function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown provider "%s", registered reindex providers: "%s"',
                $name, implode('", "', array_keys($this->providers))
            ));
        }

        return $this->providers[$name];
    }
}
