<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Reindex;

/**
 * Registry for reindex providers.
 */
class ReindexProviderRegistry
{
    /**
     * @var ReindexProviderInterface
     */
    private $providers = [];

    /**
     * Add a reindex provider to the registry.
     *
     * @param string $name
     * @param ReindexProviderInterface $provider
     *
     * @throws \InvalidArgumentException
     */
    public function addProvider($name, ReindexProviderInterface $provider)
    {
        if (isset($this->providers[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Reindex provider with name "%s" has already been registered.',
                $name
            ));
        }

        $this->providers[$name] = $provider;
    }

    /**
     * Return the registered providers.
     *
     * @return ReindexProviderInterface[]
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
