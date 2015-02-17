<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Integration;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\SearchQuery;

abstract class AdapterTestCase extends BaseTestCase
{
    const INDEXNAME = 'massive_search_test';
    const DOCCLASS = '\Some\Test\Class\Name';

    protected $factory;
    protected $adapter;
    protected $idCounter;

    public function setUp()
    {
        parent::setUp();
        $this->idCounter = 0;
        $this->factory = new Factory();
        $this->purgeIndex(self::INDEXNAME);
        $this->adapter = null;
    }

    final protected function getAdapter()
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        $this->adapter = $this->doGetAdapter();

        return $this->adapter;
    }

    /**
     * Return the testing adapter
     *
     * @return Massive\Bundle\SearchBundle\Search\AdapterInterface
     */
    abstract protected function doGetAdapter();

    /**
     * Purge the given index (or everything)
     */
    public function purgeIndex($indexName)
    {
        $this->getAdapter()->purge($indexName);
        usleep(1000);
    }

    public function testPurge()
    {
        $adapter = $this->getAdapter();
        $this->createIndex();
        $query = new SearchQuery('One');
        $query->setIndexes(array(
            self::INDEXNAME
        ));
        $res = $adapter->search($query);
        $this->assertCount(1, $res);

        $adapter->purge(self::INDEXNAME);
        $this->flush(self::INDEXNAME);

        $adapter = $this->getAdapter();
        $query = new SearchQuery('One');
        $query->setIndexes(array(
            self::INDEXNAME
        ));
        $res = $adapter->search($query);

        $this->assertCount(0, $res);
    }

    /**
     * Called after indexes are created (a good time to flush
     * the implementation if it is asyncronous)
     */
    public function flush($indexName)
    {
    }

    public function testIndexer()
    {
        $this->createIndex();

        $query = new SearchQuery('One');

        $query->setIndexes(array(
            self::INDEXNAME
        ));
        $res = $this->getAdapter()->search($query);

        $this->assertCount(1, $res);
    }

    public function provideSearch()
    {
        return array(
            array('one', 1),
            array('one ', 1),
            array('roomba 870', 0),
            array('870', 0),
            array('*', 2),
            array('***', 2),
            array('???', 2),
        );
    }

    /**
     * @dataProvider provideSearch
     */
    public function testSearch($query, $expectedNbResults)
    {
        $this->createIndex();

        $query = new SearchQuery($query);
        $query->setIndexes(array(self::INDEXNAME));
        $res = $this->getAdapter()->search($query);

        $this->assertCount($expectedNbResults, $res);
    }

    public function testGetStatistics()
    {
        $this->createIndex();
        $statistics = $this->getAdapter()->getStatus();
        $this->assertTrue(is_array($statistics));
    }

    public function testDeindex()
    {
        $this->createIndex();
        $doc = $this->factory->makeDocument();
        $doc->setId(1);
        $doc->setClass(self::DOCCLASS);
        $this->getAdapter()->deindex($doc, self::INDEXNAME);
        $this->flush(self::INDEXNAME);

        $query = new SearchQuery('One');
        $query->setIndexes(array(self::INDEXNAME));
        $res = $this->getAdapter()->search($query);

        $this->assertCount(0, $res);

        $query = new SearchQuery('Two');
        $query->setIndexes(array(self::INDEXNAME));
        $res = $this->getAdapter()->search($query);

        $this->assertCount(1, $res);
    }

    protected function createDocument($title)
    {
        $this->idCounter++;

        $document = $this->factory->makeDocument();
        $document->setId($this->idCounter);
        $document->setClass(self::DOCCLASS);
        $document->setTitle($title);
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
        $index = self::INDEXNAME;

        $documents = array(
            $this->createDocument('Document One'),
            $this->createDocument('Document Two'),
        );

        foreach ($documents as $document) {
            $this->getAdapter()->index($document, $index);
        }
    }

    protected function getFactory()
    {
        return new Factory();
    }
}
