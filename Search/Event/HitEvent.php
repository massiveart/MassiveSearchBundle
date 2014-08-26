<?php

namespace Massive\Bundle\SearchBundle\Search\Event;

use Symfony\Component\EventDispatcher\Event;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\QueryHit;

class HitEvent extends Event
{
    protected $hit;

    public function __construct(QueryHit $hit)
    {
        $this->hit = $hit;
    }

    public function getHit() 
    {
        return $this->hit;
    }
}
