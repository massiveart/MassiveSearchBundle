<?php

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Representation of one search hit
 * @package Massive\Bundle\SearchBundle\Search
 */
class QueryHit
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
}
