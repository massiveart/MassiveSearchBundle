<?php

namespace Massive\Bundle\SearchBundle\Search\Event;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Preindex event is fired before a document is indexed
 */
class PreIndexEvent extends Event
{
    protected $subject;
    protected $document;
    protected $metadata;

    /**
     * @param Document $document
     * @param IndexMetadataInterface $metadata
     */
    public function __construct($subject, Document $document, IndexMetadataInterface $metadata)
    {
        $this->subject = $subject;
        $this->document = $document;
        $this->metadata = $metadata;
    }

    /**
     * @return mixed
     */
    public function getSubject() 
    {
        return $this->subject;
    }

    /**
     * @return Document
     */
    public function getDocument() 
    {
        return $this->document;
    }

    /**
     * @return IndexMetadataInterface
     */
    public function getMetadata() 
    {
        return $this->metadata;
    }
}
