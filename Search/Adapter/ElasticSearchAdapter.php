<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Adapter;

use Elasticsearch\Client as ElasticSearchClient;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Massive\Bundle\SearchBundle\Search\SearchResult;

/**
 * ElasticSearch adapter using official client:.
 *
 * https://github.com/elasticsearch/elasticsearch-php
 */
class ElasticSearchAdapter implements AdapterInterface
{
    const ID_FIELDNAME = '__id';

    const INDEX_FIELDNAME = '__index';

    const CLASS_TAG = '__class';

    const URL_FIELDNAME = '__url';

    const TITLE_FIELDNAME = '__title';

    const DESCRIPTION_FIELDNAME = '__description';

    const LOCALE_FIELDNAME = '__locale';

    const IMAGE_URL = '__image_url';

    const DOCUMENT_TYPE = '__document_type';

    /**
     * @var \Massive\Bundle\SearchBundle\Search\Factory
     */
    private $factory;

    /**
     * @var \ElasticSearch\Client
     */
    private $client;

    /**
     * @var bool
     */
    private $indexListLoaded;

    /**
     * @var array
     */
    private $indexList;

    /**
     * @var string
     */
    private $version;

    /**
     * @param Factory $factory
     * @param ElasticSearchClient $client
     * @param string $version
     */
    public function __construct(Factory $factory, ElasticSearchClient $client, $version)
    {
        $this->factory = $factory;
        $this->client = $client;
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function index(Document $document, $indexName)
    {
        $fields = [];
        foreach ($document->getFields() as $massiveField) {
            $type = $massiveField->getType();
            $value = $massiveField->getValue();

            switch ($type) {
                case Field::TYPE_STRING:
                case Field::TYPE_ARRAY:
                    $fields[$this->encodeFieldName($massiveField->getName())] = $value;
                    break;
                case Field::TYPE_NULL:
                    // ignore it
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Search field type "%s" is not known. Known types are: %s',
                            $massiveField->getType(),
                            implode(', ', Field::getValidTypes())
                        )
                    );
            }
        }

        $documentType = $this->documentToType($document);

        $fields[self::ID_FIELDNAME] = $document->getId();
        $fields[self::INDEX_FIELDNAME] = $document->getIndex();
        $fields[self::URL_FIELDNAME] = $document->getUrl();
        $fields[self::TITLE_FIELDNAME] = $document->getTitle();
        $fields[self::DESCRIPTION_FIELDNAME] = $document->getDescription();
        $fields[self::LOCALE_FIELDNAME] = $document->getLocale();
        $fields[self::CLASS_TAG] = $document->getClass();
        $fields[self::IMAGE_URL] = $document->getImageUrl();
        $fields[self::DOCUMENT_TYPE] = $documentType;

        // ensure that any new index name is listed when calling listIndexes
        $this->indexList[$indexName] = $indexName;

        $params = [
            'id' => $document->getId(),
            'type' => $indexName,
            'index' => $indexName,
            'body' => $fields,
        ];

        // for BC we still set for older elasticsearch versions the type attribute to the documentType
        // can be removed when min requirement of elasticsearch >= 6.0
        if (version_compare($this->version, '6.0', '<')) {
            $params['type'] = $documentType;
        }

        $this->client->index($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deindex(Document $document, $indexName)
    {
        $params = [
            'index' => $indexName,
            'type' => $indexName,
            'id' => $document->getId(),
            'refresh' => true,
        ];

        // for BC we still set for older elasticsearch versions the type attribute to the documentType
        // can be removed when min requirement of elasticsearch >= 6.0
        if (version_compare($this->version, '6.0', '<')) {
            $params['type'] = $this->documentToType($document);
        }

        try {
            $this->client->delete($params);
        } catch (Missing404Exception $e) {
            // ignore 404 exceptions
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchQuery $searchQuery)
    {
        $indexNames = $searchQuery->getIndexes();

        $queryString = $searchQuery->getQueryString();

        $params['index'] = implode(',', $indexNames);
        $params['body'] = [
            'query' => [
                'query_string' => [
                    'query' => $queryString,
                ],
            ],
            'from' => $searchQuery->getOffset(),
        ];
        if (!empty($searchQuery->getLimit())) {
            $params['body']['size'] = $searchQuery->getLimit();
        }

        foreach ($searchQuery->getSortings() as $sort => $order) {
            $params['body']['sort'][] = [
                $sort => [
                    'order' => $order,
                ],
            ];
        }

        $res = $this->client->search($params);
        $elasticHits = $res['hits']['hits'];

        $hits = [];

        foreach ($elasticHits as $elasticHit) {
            $hit = $this->factory->createQueryHit();
            $document = $this->factory->createDocument();

            $hit->setDocument($document);
            $hit->setScore($elasticHit['_score']);

            $document->setId($elasticHit['_id']);

            $elasticSource = $elasticHit['_source'];

            if (isset($elasticSource[self::INDEX_FIELDNAME])) {
                $document->setIndex($elasticSource[self::INDEX_FIELDNAME]);
            }
            if (isset($elasticSource[self::TITLE_FIELDNAME])) {
                $document->setTitle($elasticSource[self::TITLE_FIELDNAME]);
            }
            if (isset($elasticSource[self::DESCRIPTION_FIELDNAME])) {
                $document->setDescription($elasticSource[self::DESCRIPTION_FIELDNAME]);
            }
            if (isset($elasticSource[self::LOCALE_FIELDNAME])) {
                $document->setLocale($elasticSource[self::LOCALE_FIELDNAME]);
            }
            if (isset($elasticSource[self::URL_FIELDNAME])) {
                $document->setUrl($elasticSource[self::URL_FIELDNAME]);
            }
            if (isset($elasticSource[self::CLASS_TAG])) {
                $document->setClass($elasticSource[self::CLASS_TAG]);
            }
            if (isset($elasticSource[self::IMAGE_URL])) {
                $document->setImageUrl($elasticSource[self::IMAGE_URL]);
            }

            $hit->setId($document->getId());

            foreach ($elasticSource as $fieldName => $fieldValue) {
                $document->addField($this->factory->createField($this->decodeFieldName($fieldName), $fieldValue));
            }
            $hits[] = $hit;
        }

        return new SearchResult($hits, $res['hits']['total']);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        $indices = $this->getIndexStatus();
        $indexes = $indices['indices'];
        $status = [];

        foreach ($indexes as $indexName => $index) {
            foreach ($index as $field => $value) {
                $status['idx:' . $indexName . '.' . $field] = substr(trim(json_encode($value)), 0, 100);
            }
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($indexName)
    {
        try {
            $this->client->indices()->delete(['index' => $indexName]);
            $this->indexListLoaded = false;
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listIndexes()
    {
        if (!$this->indexListLoaded) {
            $indices = $this->getIndexStatus();
            $indexes = $indices['indices'];
            $this->indexList = array_combine(
                array_keys($indexes),
                array_keys($indexes)
            );
            $this->indexListLoaded = true;
        }

        return $this->indexList;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(array $indexNames)
    {
        $this->client->indices()->flush(
            [
                'index' => implode(', ', $indexNames),
                'full' => true,
            ]
        );
    }

    /**
     * Convert FQCN to a snake-case string to use as an
     * elastic search type.
     *
     * @param Document $document
     *
     * @return string
     */
    private function documentToType(Document $document)
    {
        $class = $document->getClass();

        if (!$class) {
            return 'massive_undefined';
        }

        return ltrim(str_replace('\\', '_', $class), '_');
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        // currently the elastic search adapter does not need any initialization
        // might make sense to create some schema stuff here
    }

    /**
     * Returns index-status.
     *
     * @return array
     */
    private function getIndexStatus()
    {
        if (version_compare($this->version, '2.2', '>')) {
            return $this->client->indices()->stats(['index' => '_all']);
        }

        return $this->client->indices()->status(['index' => '_all']);
    }

    /**
     * Returns encoded field-name.
     *
     * @param string $fieldName
     *
     * @return string
     */
    private function encodeFieldName($fieldName)
    {
        return str_replace('.', '#', $fieldName);
    }

    /**
     * Returns decoded field-name.
     *
     * @param string $fieldName
     *
     * @return string
     */
    private function decodeFieldName($fieldName)
    {
        return str_replace('#', '.', $fieldName);
    }
}
