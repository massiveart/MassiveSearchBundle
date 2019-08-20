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

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * PreDeindex event is fired before a document is deindexed.
 */
class PreDeindexEvent extends Event
{
    /**
     * The object, which has been deindexed.
     *
     * @var object
     */
    private $subject;

    /**
     * The search document, which is the result of the deindexing.
     *
     * @var Document
     */
    private $document;

    /**
     * The metadata, on which the deindex process has been based.
     *
     * @var IndexMetadataInterface
     */
    private $metadata;

    /**
     * @param mixed $subject
     * @param Document $document
     * @param IndexMetadataInterface $metadata
     */
    public function __construct(
        $subject,
        Document $document,
        IndexMetadataInterface $metadata
    ) {
        $this->subject = $subject;
        $this->document = $document;
        $this->metadata = $metadata;
    }

    /**
     * Returns the deindexed subject.
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the document, which was the result of the deindexed object.
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns the metadata based on which the deindexing was done.
     *
     * @return IndexMetadataInterface
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
