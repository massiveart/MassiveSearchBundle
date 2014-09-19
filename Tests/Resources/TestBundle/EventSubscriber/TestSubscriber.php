<?php

namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;

class TestSubscriber implements EventSubscriberInterface
{
    public $hitDocument;
    public $documentReflection;
    public $nbHits = 0;

    public $preIndexDocument;
    public $preIndexMetadata;

    public static function getSubscribedEvents()
    {
        return array(
            SearchEvents::HIT => 'handleHit',
            SearchEvents::PRE_INDEX => 'handlePreIndex',
        );
    }

    public function handleHit(HitEvent $e)
    {
        $this->hitDocument = $e->getHit()->getDocument();;
        $this->hitDocument->setTitle('My title');
        $this->hitDocument->setDescription('My description');
        $this->hitDocument->setUrl('/example');
        $this->documentReflection = $e->getDocumentReflection();

        $this->nbHits++;
    }

    public function handlePreIndex(PreIndexEvent $e)
    {
        $this->preIndexDocument = $e->getDocument();
        $this->preIndexMetadata = $e->getMetadata();
    }
}
