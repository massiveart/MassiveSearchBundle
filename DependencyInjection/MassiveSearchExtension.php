<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MassiveSearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setAlias('massive_search.factory', $config['services']['factory']);

        $loader->load('command.xml');
        $this->loadSearch($config, $loader, $container);
        $this->loadMetadata($config['metadata'], $loader, $container);
        $this->loadPersistence($config['persistence'], $loader);
    }

    private function loadPersistence($config, Loader\XmlFileLoader $loader)
    {
        foreach ($config as $persistenceName => $config) {
            if (false === $config['enabled']) {
                continue;
            }

            $loader->load($persistenceName . '.xml');
        }
    }

    private function loadSearch($config, Loader\XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setAlias('massive_search.adapter', 'massive_search.adapter.' . $config['adapter']);
        $loader->load('search.xml');

        switch ($config['adapter']) {
            case 'zend_lucene':
                $this->loadZendSearch($config['adapters']['zend_lucene'], $loader, $container);
                break;
            case 'elastic':
                $this->loadElasticSearch($config['adapters']['elastic'], $loader, $container);
                break;
        }
    }

    private function loadZendSearch($config, Loader\XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter('massive_search.adapter.zend_lucene.basepath', $config['basepath']);
        $container->setParameter('massive_search.adapter.zend_lucene.hide_index_exception', $config['hide_index_exception']);
        $container->setParameter('massive_search.adapter.zend_lucene.encoding', $config['encoding']);
        $loader->load('adapter_zendlucene.xml');
    }

    private function loadElasticSearch($config, Loader\XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter('massive_search.adapter.elastic.hosts', $config['hosts']);
        $container->setParameter('massive_search.adapter.elastic.version', $config['version']);
        $loader->load('adapter_elastic.xml');

        if (!class_exists($container->getParameter('massive_search.search.adapter.elastic.client.class'))) {
            throw new \RuntimeException(
                'Cannot find elastic search client class -- have you installed the elasticsearch/elasticsearch package?'
            );
        }
    }

    private function loadMetadata($config, Loader\XmlFileLoader $loader, ContainerBuilder $container)
    {
        $dir = $container->getParameterBag()->resolveValue($config['cache_dir']);
        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        }
        $container->setParameter('massive_search.metadata.prefix', $config['prefix']);
        $container->setParameter('massive_search.metadata.cache_dir', $config['cache_dir']);
        $container->setParameter('massive_search.metadata.debug', $config['debug']);

        $loader->load('metadata.xml');

        $metadataPaths = $this->getBundleMappingPaths($container->getParameter('kernel.bundles'));

        $kernelProjectDir = $container->getParameter('kernel.project_dir');
        foreach (['Entity', 'Document', 'Model'] as $entityNamespace) {
            if (!file_exists($kernelProjectDir . '/src/' . $entityNamespace)) {
                continue;
            }

            $namespace = 'App\\' . $entityNamespace;
            $metadataPaths[$namespace] = $kernelProjectDir . '/config/massive-search';
        }

        $fileLocator = $container->getDefinition('massive_search.metadata.file_locator');
        $fileLocator->replaceArgument(0, $metadataPaths);
    }

    private function getBundleMappingPaths($bundles)
    {
        $metadataPaths = [];
        foreach ($bundles as $bundle) {
            $refl = new \ReflectionClass($bundle);
            $path = dirname($refl->getFilename());

            foreach (['Entity', 'Document', 'Model'] as $entityNamespace) {
                if (!file_exists($path . '/' . $entityNamespace)) {
                    continue;
                }

                $namespace = $refl->getNamespaceName() . '\\' . $entityNamespace;
                $finalPath = implode('/', [$path, 'Resources', 'config', 'massive-search']);
                if (!file_exists($finalPath)) {
                    continue;
                }

                $metadataPaths[$namespace] = $finalPath;
            }
        }

        return $metadataPaths;
    }
}
