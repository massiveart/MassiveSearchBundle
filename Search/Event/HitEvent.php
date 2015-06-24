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

use Symfony\Component\EventDispatcher\Event;
use Massive\Bundle\SearchBundle\Search\QueryHit;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;

class HitEvent extends Event
{
    protected $hit;
    protected $metadata;

    public function __construct(QueryHit $hit, ClassMetadata $metadata)
    {
        $this->hit = $hit;
        $this->metadata = $metadata;
    }

    public function getHit()
    {
        return $this->hit;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}
