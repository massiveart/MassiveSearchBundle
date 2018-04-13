<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber;

use Massive\Bundle\SearchBundle\Search\Event\HitEvent;
use Massive\Bundle\SearchBundle\Search\Event\PreIndexEvent;
use Massive\Bundle\SearchBundle\Search\SearchEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestSubscriber implements EventSubscriberInterface
{
    public $hitDocument;

    public $documentReflection;

    public $nbHits = 0;

    public $preIndexDocument;

    public $preIndexMetadata;

    public static function getSubscribedEvents()
    {
        return [
            SearchEvents::HIT => 'handleHit',
            SearchEvents::PRE_INDEX => 'handlePreIndex',
        ];
    }

    public function handleHit(HitEvent $e)
    {
        $this->hitDocument = $e->getHit()->getDocument();
        $this->hitDocument->setTitle('My title');
        $this->hitDocument->setDescription('My description');
        $this->hitDocument->setUrl('/example');
        $this->documentReflection = $e->getDocumentReflection();

        ++$this->nbHits;
    }

    public function handlePreIndex(PreIndexEvent $e)
    {
        $this->preIndexDocument = $e->getDocument();
        $this->preIndexMetadata = $e->getMetadata();
    }
}
