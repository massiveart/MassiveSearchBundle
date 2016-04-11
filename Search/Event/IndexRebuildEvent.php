<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is dispatched when the rebuild index command
 * is executed.
 */
class IndexRebuildEvent extends Event
{
    /**
     * @var string
     */
    private $filter;

    /**
     * @var bool
     */
    private $purge;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param mixed $filter Regex filter for the object class
     * @param mixed $purge If the indexes should be purged
     * @param OutputInterface $output
     */
    public function __construct($filter, $purge, OutputInterface $output = null)
    {
        $this->filter = $filter;
        $this->purge = $purge;
        $this->output = $output ?: new NullOutput();
    }

    /**
     * Return the regex filter to apply to the
     * class names of the indexed documents in order
     * to determine which classes get rebuilt.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * If each affected index should be purged before rebuilding.
     *
     * @return bool
     */
    public function getPurge()
    {
        return $this->purge;
    }

    /**
     * Return the console Output class.
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}
