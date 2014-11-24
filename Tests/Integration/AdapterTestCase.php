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
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\SearchQuery;

abstract class AdapterTestCase extends BaseTestCase
{
    protected $factory;
    protected $adapter;

    public function setUp()
    {
        $this->factory = new Factory();
        parent::setUp();
        $this->adapter = $this->getAdapter();
    }

    public function testIndexer()
    {
        $this->createIndex();

        $query = new SearchQuery('one');
        $query->setIndexes(array('foobar'));
        $res = $this->adapter->search($query);

        $this->assertCount(1, $res);
    }

    public function provideSearch()
    {
        return array(
            array('one', 1),
            array('one ', 1),
            array('roomba 870', 0),
            array('870', 0),
            array('*', 0),
            array('***', 0),
            array('???', 0),
        );
    }

    /**
     * @dataProvider provideSearch
     */
    public function testSearch($query, $expectedNbResults)
    {
        $this->createIndex();

        $query = new SearchQuery($query);
        $query->setIndexes(array('foobar'));
        $res = $this->adapter->search($query);

        $this->assertCount($expectedNbResults, $res);
    }

    public function provideSearchSpecialCases()
    {
        return array(
            array('Foobar', 'Barfoo', 0),
            array('Foobar', 'Foobar', 1),
            array('T1000', 'T1000', 1),
            array('Item 100', 'Item 100', 1),
            array('Item 100', 'ITEM 100', 1),
            array('Item 100', '100', 1),
            array('100', '100', 1),
        );
    }

    /**
     * @dataProvider provideSearchSpecialCases
     */
    public function testSearchSpecialCases($documentTitle, $query, $expectedNbResults)
    {
        $this->adapter = $this->getAdapter();
        $document = $this->createDocument($documentTitle);
        $this->adapter->index($document, 'foobar');

        $query = new SearchQuery($query);
        $query->setIndexes(array('foobar'));
        $res = $this->adapter->search($query);

        $this->assertCount($expectedNbResults, $res);
    }

    public function testGetStatistics()
    {
        $this->createIndex();
        $statistics = $this->adapter->getStatus();
        $this->assertTrue(is_array($statistics));
    }

    public function testDeindex()
    {
        $this->createIndex();
        $doc = $this->factory->makeDocument();
        $doc->setId(1);
        $this->adapter->deindex($doc, 'foobar');

        $query = new SearchQuery('document');
        $query->setIndexes(array('foobar'));
        $res = $this->adapter->search($query);

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
        $this->adapter = $this->getAdapter();
        $documents = array(
            $this->createDocument('Document One'),
            $this->createDocument('Document Two'),
        );

        foreach ($documents as $document) {
            $this->adapter->index($document, 'foobar');
        }
    }

    protected function getFactory()
    {
        return new Factory();
    }
}
