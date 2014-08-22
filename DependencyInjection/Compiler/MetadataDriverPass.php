<?php

namespace Massive\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register metadata drivers
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class MetadataDriverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'massive_search.metadata.driver.chain'
        )) {
            return;
        }

        $driverChainDef = $container->getDefinition(
            'massive_search.metadata.driver.chain'
        );

        $ids = $container->findTaggedServiceIds('massive_search.metadata.driver');
        $serviceRefs = array();

        foreach (array_keys($ids) as $id) {
            $serviceRefs[] = new Reference($id);
        }

        $driverChainDef->addArgument($serviceRefs);
    }
}
