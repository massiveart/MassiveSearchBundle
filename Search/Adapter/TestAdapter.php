<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Adapter;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Massive\Bundle\SearchBundle\Search\Factory;

/**
 * Test adapter for testing scenarios
 *
 * @author Daniel Leech <daniel@massive.com>
 */
class TestAdapter implements AdapterInterface
{
    protected $documents = array();
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function index(Document $document, $indexName)
    {
        $this->documents[] = $document;
    }

    public function deindex(Document $document, $indexName)
    {
        foreach ($this->documents as $i => $selfDocument) {
            if ($document === $selfDocument) {
                unset($this->documents[$i]);
            }
        }

        $this->documents = array_values($this->documents);
    }

    /**
     * Return all the "indexed" documents
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * {@inheritDoc}
     */
    public function search(SearchQuery $searchQuery)
    {
        $hits = array();

        foreach ($this->documents as $document) {
            $hit = $this->factory->makeQueryHit();
            $hit->setDocument($document);
            $hit->setScore(-1);
            $hits[] = $hit;
        }

        return $hits;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return array();
    }
}
