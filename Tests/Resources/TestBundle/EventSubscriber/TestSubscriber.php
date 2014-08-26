<?php

namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;

class TestSubscriber implements EventSubscriberInterface
{
    public $hitDocument;
    public $nbHits = 0;

    public static function getSubscribedEvents()
    {
        return array(
            SearchEvents::HIT => 'handleHit',
        );
    }

    public function handleHit(HitEvent $e)
    {
        $this->hitDocument = $e->getHit()->getDocument();;
        $this->hitDocument->setTitle('My title');
        $this->hitDocument->setDescription('My description');
        $this->hitDocument->setUrl('/example');

        $this->nbHits++;
    }
}
