<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Compiler pass for all zend search related configuration
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ZendLucenePass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processAnalyzer($container);
    }

    /**
     * Collect all the tagged analyzer services and add a reference
     * to the selected one to the adapter definition
     *
     * @param ContainerBuilder $container
     */
    private function processAnalyzer(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'massive_search.adapter.zend_lucene'
        )) {
            return;
        }

        $zendLuceneDef = $container->getDefinition(
            'massive_search.adapter.zend_lucene'
        );

        $ids = $container->findTaggedServiceIds('massive_search.zend_lucene.analyzer');
        $serviceRefs = array();

        foreach ($ids as $id => $attrs) {
            if (!isset($attrs[0]['alias'])) {
                throw new InvalidArgumentException(sprintf(
                    'Tag for service "%s" must have the alias attribute defined',
                    $id
                ));
            }

            $serviceRefs[$attrs[0]['alias']] = new Reference($id);
        }

        $analyzerAlias = $container->getParameter('massive_search.zend_lucene.analyzer_alias');

        if (!isset($serviceRefs[$analyzerAlias])) {
            throw new InvalidArgumentException(sprintf(
                'Analyzer alias "%s" is not valid. Known analyzer aliases are: "%s"',
                $analyzerAlias, implode('", "', array_keys($serviceRefs))
            ));
        }

        $zendLuceneDef->replaceArgument(2, new Reference($serviceRefs[$analyzerAlias]));
    }
}
