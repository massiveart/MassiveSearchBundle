<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Representation of one search hit.
 */
class QueryHit implements \JsonSerializable
{
    protected $document;

    protected $score;

    protected $id;

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Document $document
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @return number
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param number $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'document' => $this->document->jsonSerialize(),
            'score' => $this->score,
        ];
    }
}
