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
use Massive\Bundle\SearchBundle\Search\Metadata\FieldEvaluator;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;

/**
 * Preindex event is fired before a document is indexed.
 */
class PreIndexEvent extends AbstractEvent
{
    /**
     * The object, which has been indexed.
     *
     * @var object
     */
    private $subject;

    /**
     * The search document, which is the result of the indexing.
     *
     * @var Document
     */
    private $document;

    /**
     * The metadata, on which the index process has been based.
     *
     * @var IndexMetadataInterface
     */
    private $metadata;

    /**
     * The field evaluator.
     *
     * @var FieldEvaluator
     */
    private $fieldEvaluator;

    /**
     * @param mixed $subject
     * @param Document $document
     * @param IndexMetadataInterface $metadata
     * @param FieldEvaluator $fieldEvaluator
     */
    public function __construct(
        $subject,
        Document $document,
        IndexMetadataInterface $metadata,
        FieldEvaluator $fieldEvaluator
    ) {
        $this->subject = $subject;
        $this->document = $document;
        $this->metadata = $metadata;
        $this->fieldEvaluator = $fieldEvaluator;
    }

    /**
     * Returns the indexed subject.
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the document, which is the result of the indexed object.
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns the metadata based on which the indexing was done.
     *
     * @return IndexMetadataInterface
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Return the field evaluator.
     *
     * @return FieldEvaluator
     */
    public function getFieldEvaluator()
    {
        return $this->fieldEvaluator;
    }
}
