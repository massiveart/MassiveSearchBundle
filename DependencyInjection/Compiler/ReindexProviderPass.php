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
class ReindexProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'massive_search.reindex.provider_registry'
        )) {
            return;
        }

        $registry = $container->getDefinition(
            'massive_search.reindex.provider_registry'
        );

        $tagName = 'massive_search.reindex.provider';
        $ids = $container->findTaggedServiceIds($tagName);

        foreach ($ids as $id => $attributes) {
            if (!isset($attributes[0]['id'])) {
                throw new \InvalidArgumentException(sprintf(
                    'All %s tags must include the "id" attribute.',
                    $tagName
                ));
            }

            $registry->addMethodCall('addProvider', [$attributes[0]['id'], new Reference($id)]);
        }
    }
}
