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

/**
 * Compiler pass to add additional paths based on the kernel namespace.
 */
class MetadataFileLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'massive_search.metadata.file_locator'
        )) {
            return;
        }
        $kernelClass = $container->getDefinition('kernel')->getClass();
        if ('App\\Kernel' === $kernelClass) {
            return; // App paths already set in MassiveSearchExtension::loadMetadata
        }

        $projectNamespace = substr($kernelClass, 0, strrpos($kernelClass, '\\'));
        $kernelProjectDir = $container->getParameter('kernel.project_dir');
        $kernelDirectory = dirname((new \ReflectionClass($kernelClass))->getFileName());

        $fileLocator = $container->getDefinition('massive_search.metadata.file_locator');
        $metadataPaths = $fileLocator->getArgument(0);

        foreach (['Entity', 'Document', 'Model'] as $entityNamespace) {
            if (!file_exists($kernelDirectory . '/' . $entityNamespace)) {
                continue;
            }

            $namespace = $projectNamespace . '\\' . $entityNamespace;
            $metadataPaths[$namespace] = $kernelProjectDir . '/config/massive-search';
        }

        $fileLocator->replaceArgument(0, $metadataPaths);
    }
}
