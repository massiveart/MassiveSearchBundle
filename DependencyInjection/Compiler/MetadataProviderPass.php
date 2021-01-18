<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register metadata providers.
 */
class MetadataProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'massive_search.metadata.provider.chain'
        )) {
            return;
        }

        $driverChainDef = $container->getDefinition(
            'massive_search.metadata.provider.chain'
        );

        $ids = $container->findTaggedServiceIds('massive_search.metadata.provider');
        $serviceRefs = [];

        foreach (\array_keys($ids) as $id) {
            $serviceRefs[] = new Reference($id);
        }

        $driverChainDef->replaceArgument(0, $serviceRefs);
    }
}
