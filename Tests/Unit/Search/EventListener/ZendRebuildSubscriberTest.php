<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\Search\EventListener;

use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;
use Massive\Bundle\SearchBundle\Search\Decorator\IndexNameDecoratorInterface;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\EventListener\ZendRebuildSubscriber;
use Symfony\Component\Console\Output\OutputInterface;

class ZendRebuildSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ZendLuceneAdapter
     */
    private $adapter;

    /**
     * @var IndexNameDecoratorInterface
     */
    private $indexNameDecorator;

    /**
     * @var IndexRebuildEvent
     */
    private $event;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ZendRebuildSubscriber
     */
    private $zendRebuildSubscriber;

    public function setUp()
    {
        $this->adapter = $this->prophesize(ZendLuceneAdapter::class);
        $this->indexNameDecorator = $this->prophesize(IndexNameDecoratorInterface::class);
        $this->event = $this->prophesize(IndexRebuildEvent::class);
        $this->output = $this->prophesize(OutputInterface::class);

        $this->event->getOutput()->willReturn($this->output->reveal());

        $this->zendRebuildSubscriber = new ZendRebuildSubscriber(
            $this->adapter->reveal(),
            $this->indexNameDecorator->reveal()
        );
    }

    public function provideIndexRebuild()
    {
        return [
            [[['my_index', true], ['my_other_index', false]]],
        ];
    }

    /**
     * @dataProvider provideIndexRebuild
     */
    public function testOnIndexRebuild($indexes)
    {
        $indexNames = [];
        foreach ($indexes as $index) {
            $this->indexNameDecorator->isVariant($index[0], $index[0])->willReturn($index[1]);
            $this->indexNameDecorator->undecorate($index[0])->willReturn($index[0]);

            if ($index[1]) {
                $this->adapter->optimize($index[0])->shouldBeCalled();
            } else {
                $this->adapter->optimize($index[0])->shouldNotBeCalled();
            }

            $indexNames[] = $index[0];
        }
        $this->adapter->listIndexes()->willReturn($indexNames);

        $this->zendRebuildSubscriber->onIndexRebuild($this->event->reveal());
    }
}
