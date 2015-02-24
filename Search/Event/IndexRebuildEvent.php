<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

class IndexRebuildEvent extends Event
{
    /**
     * @param mixed $filter Regex filter for the object class
     * @param mixed $purge If the indexes should be purged
     * @param OutputInterface $output
     */
    public function __construct($filter, $purge, OutputInterface $output = null)
    {
        $this->filter = $filter;
        $this->purge = $purge;
        $this->output = $output ? : new NullOutput();
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getPurge()
    {
        return $this->purge;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
