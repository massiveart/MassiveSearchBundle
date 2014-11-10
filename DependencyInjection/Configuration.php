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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * Returns the config tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('massive_search')
            ->children()
                ->arrayNode('services')
                    ->addDefaultsifNotSet()
                    ->children()
                        ->scalarNode('factory')->defaultValue('massive_search.factory_default')->end()
                    ->end()
                ->end()
                ->enumNode('adapter_id')
                    ->values(array('zend_lucene', 'elastic'))
                    ->defaultValue('zend_lucene')->end()
                ->arrayNode('adapters')
                    ->addDefaultsifNotSet()
                    ->children()
                        ->arrayNode('zend_lucene')
                            ->addDefaultsifNotSet()
                            ->children()
                                ->booleanNode('hide_index_exception')->defaultValue(false)->end()
                                ->scalarNode('basepath')->defaultValue('%kernel.root_dir%/data')->end()
                            ->end()
                        ->end()
                        ->arrayNode('elastic')
                            ->children()
                            ->arrayNode('hosts')
                                ->defaultValue(array('localhost:9200'))
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
