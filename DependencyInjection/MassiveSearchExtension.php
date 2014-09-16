<?php
/*
 * This file is part of the Massive CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MassiveSearchExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setAlias('massive_search.factory', $config['services']['factory']);

        $this->loadSearch($config, $loader, $container);
        $this->loadMetadata($config, $loader, $container);
    }

    protected function loadSearch($config, $loader, $container)
    {
        $container->setAlias('massive_search.adapter', $config['adapter_id']);
        $container->setParameter('massive_search.adapter.zend_lucene.basepath', $config['adapters']['zend_lucene']['basepath']);

        $loader->load('search.xml');
    }

    protected function loadMetadata($config, $loader, $container)
    {
        $loader->load('metadata.xml');

        $bundles = $container->getParameter('kernel.bundles');

        $metadataPaths = array();
        foreach ($bundles as $bundle) {
            $refl = new \ReflectionClass($bundle);
            $path = dirname($refl->getFilename());

            foreach (array('Entity', 'Document', 'Model') as $entityNamespace) {
                if (!file_exists($path . '/' . $entityNamespace)) {
                    continue;
                }

                $namespace = $refl->getNamespaceName() . '\\' . $entityNamespace;
                $metadataPaths[$namespace] = join('/', array($path, 'Resources', 'config', 'massive-search'));
            }
        }

        $fileLocator = $container->getDefinition('massive_search.metadata.file_locator');
        $fileLocator->replaceArgument(0, $metadataPaths);
    }
}
