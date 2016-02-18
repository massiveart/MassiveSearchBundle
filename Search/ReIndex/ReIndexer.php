<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\ReIndex;

class Reindexer implements ResumeManagerInterface
{
    private $resumeManager;
    private $registry;
    private $manager;

    public function __construct(
        ResumeManager $resumeManager,
        ReIndexProviderRegistry $registry,
        SearchManager $manager
    )
    {
        $this->resumeManager = $resumeManager;
        $this->registry = $registry;
        $this->manager = $manager;
    }

    public function reindex()
    {

    }
}

