<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\EventListener;

use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;
use Massive\Bundle\SearchBundle\Search\Decorator\IndexNameDecoratorInterface;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;

class ZendRebuildSubscriber
{
    /**
     * @var ZendLuceneAdapter
     */
    private $adapter;

    /**
     * @var IndexNameDecoratorInterface
     */
    private $indexNameDecorator;

    public function __construct(ZendLuceneAdapter $adapter, IndexNameDecoratorInterface $indexNameDecorator)
    {
        $this->adapter = $adapter;
        $this->indexNameDecorator = $indexNameDecorator;
    }

    /**
     * Optimize the search indexes after the index rebuild event has been fired.
     * Should have a priority low enough in order for it to be executed after all
     * the actual index builders.
     *
     * @param IndexRebuildEvent $event
     */
    public function onIndexRebuild(IndexRebuildEvent $event)
    {
        foreach ($this->adapter->listIndexes() as $indexName) {
            if (!$this->indexNameDecorator->isVariant($this->indexNameDecorator->undecorate($indexName), $indexName)) {
                continue;
            }

            $event->getOutput()->writeln(sprintf('<info>Optimizing zend lucene index:</info> %s', $indexName));
            $this->adapter->optimize($indexName);
        }
    }
}
