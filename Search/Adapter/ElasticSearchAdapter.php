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

use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\QueryHit;
use Symfony\Component\Finder\Finder;
use ZendSearch\Lucene;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Elasticsearch\Client as ElasticSearchClient;

/**
 * ElasticSearch adapter using official client:
 *
 * https://github.com/elasticsearch/elasticsearch-php
 *
 * @author Daniel Leech <daniel@massive.com>
 */
class ElasticSearchAdapter implements AdapterInterface
{
    const ID_FIELDNAME = '__id';
    const CLASS_TAG = '__class';

    // TODO: This fields should be handled at a higher level
    const URL_FIELDNAME = '__url';
    const TITLE_FIELDNAME = '__title';
    const DESCRIPTION_FIELDNAME = '__description';
    const IMAGE_URL = '__image_url';

    /**
     * @var \Massive\Bundle\SearchBundle\Search\Factory
     */
    private $factory;

    /**
     * @var \ElasticSearch\Client
     */
    private $client;

    /**
     * @param string $basePath Base filesystem path for the index
     */
    public function __construct(Factory $factory, ElasticSearchClient $client)
    {
        $this->factory = $factory;
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function index(Document $document, $indexName)
    {

        $fields = array();
        foreach ($document->getFields() as $massiveField) {
            $fields = array();
            switch ($massiveField->getType()) {
                case Field::TYPE_STRING:
                    $fields[$massiveField->getName()] = $massiveField->getValue();
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Search field type "%s" is not know. Known types are: %s',
                        implode(', ', Field::getValidTypes())
                    ));
            }
        }

        $params = array(
            'id' => $document->getId(),
            'index' => $indexName,
            'type' => 'string',
        );

        $fields[self::URL_FIELDNAME] = $document->getUrl();
        $fields[self::TITLE_FIELDNAME] = $document->getTitle();
        $fields[self::DESCRIPTION_FIELDNAME] = $document->getDescription();
        $fields[self::CLASS_TAG] = $document->getClass();
        $fields[self::IMAGE_URL] = $document->getImageUrl();

        $params['body'] = $fields;

        $this->client->index($params);
    }

    /**
     * {@inheritDoc}
     */
    public function deindex(Document $document, $indexName)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function search(SearchQuery $searchQuery)
    {
        $indexNames = $searchQuery->getIndexes();
        $locale = $searchQuery->getLocale();
        $queryString = $searchQuery->getQueryString();

        foreach ($indexNames as $indexName) {

            // I AM HERE
            //
        }

        $luceneHits = $searcher->find($query);

        $hits = array();

        foreach ($luceneHits as $luceneHit) {
            $hit = $this->factory->makeQueryHit();
            $document = $this->factory->makeDocument();

            $hit->setDocument($document);
            $hit->setScore($luceneHit->score);

            $luceneDocument = $luceneHit->getDocument();

            // map meta fields to document
            $document->setId($luceneDocument->getFieldValue(self::ID_FIELDNAME));
            $document->setTitle($luceneDocument->getFieldValue(self::TITLE_FIELDNAME));
            $document->setDescription($luceneDocument->getFieldValue(self::DESCRIPTION_FIELDNAME));
            $document->setUrl($luceneDocument->getFieldValue(self::URL_FIELDNAME));
            $document->setClass($luceneDocument->getFieldValue(self::CLASS_TAG));
            $document->setImageUrl($luceneDocument->getFieldValue(self::IMAGE_URL));

            $hit->setId($document->getId());

            foreach ($luceneDocument->getFieldNames() as $fieldName) {
                $document->addField($this->factory->makeField($fieldName, $luceneDocument->getFieldValue($fieldName)));
            }
            $hits[] = $hit;
        }

        return $hits;
    }

    public function getStatus()
    {
        $finder = new Finder();
        $indexDirs = $finder->directories()->depth('== 0')->in($this->basePath);
        $status = array();

        foreach ($indexDirs as $indexDir) {
            $indexFinder = new Finder();
            $files = $indexFinder->files()->name('*')->depth('== 0')->in($indexDir->getPathname());
            $indexName = basename($indexDir);

            $index = Lucene\Lucene::open($this->getIndexPath($indexName));

            $indexStats = array(
                'size' => 0,
                'nb_files' => 0,
                'nb_documents' => $index->count()
            );

            foreach ($files as $file) {
                $indexStats['size'] += filesize($file);
                $indexStats['nb_files']++;
            }

            $status['idx:' . $indexName] = json_encode($indexStats);
        }

        return $status;
    }
}

