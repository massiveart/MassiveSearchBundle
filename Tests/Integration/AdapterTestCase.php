<?php

namespace Massive\Bundle\SearchBundle\Tests\Integration;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\SearchQuery;

abstract class AdapterTestCase extends BaseTestCase
{
    protected $factory;

    public function setUp()
    {
        $this->factory = new Factory();
        parent::setUp();
    }

    public function testIndexer()
    {
        $adapter = $this->getAdapter();
        $this->createIndex();

        $query = new SearchQuery('one');
        $query->setIndexes(array('foobar'));
        $res = $adapter->search($query);

        $this->assertCount(1, $res);
    }

    public function testGetStatistics()
    {
        $this->createIndex();
        $adapter = $this->getAdapter();
        $statistics = $adapter->getStatus();
        $this->assertTrue(is_array($statistics));
    }

    public function testDeindex()
    {
        $this->createIndex();
        $doc = $this->factory->makeDocument();
        $doc->setId(1);
        $this->getAdapter()->deindex($doc, 'foobar');

        $query = new SearchQuery('document');
        $query->setIndexes(array('foobar'));
        $res = $this->getAdapter()->search($query);

        // this should be one, but the lucene index needs to be
        // comitted, and to do that we must callits destruct method.
        $this->assertCount(2, $res);
    }

    protected function createDocument($title)
    {
        static $id = 0;
        $id++;

        $document = $this->factory->makeDocument();
        $document->setId($id);
        $document->addField($this->factory->makeField('title', $title, 'string'));
        $text = <<<EOT
This section is a brief introduction to reStructuredText (reST) concepts and syntax, intended to provide authors with enough information to author documents documentively. Since reST was designed to be a simple, unobtrusive markup language, this will not take too long.
EOT
        ;
        $document->addField($this->factory->makeField('body', $text, 'string'));

        return $document;
    }

    protected function createIndex()
    {
        $adapter = $this->getAdapter();
        $documents = array(
            $this->createDocument('Document One'),
            $this->createDocument('Document Two'),
        );

        foreach ($documents as $document) {
            $adapter->index($document, 'foobar');
        }
    }

    protected function getFactory()
    {
        return new Factory();
    }
}
