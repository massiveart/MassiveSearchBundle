<?php

namespace Massive\Bundle\SearchBundle\Search\Adapter;

use ZendSearch\Lucene;
use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\QueryHit;

/**
 * Adapter for the ZendSearch library
 *
 * https://github.com/zendframework/ZendSearch
 * http://framework.zend.com/manual/1.12/en/zend.search.lucene.html 
 *   (docs for 1.2 version apply equally to 2.0)
 *
 * @author Daniel Leech <daniel@massive.com>
 */
class ZendLuceneAdapter implements AdapterInterface
{
    const ID_FIELDNAME = '__id';
    const URL_FIELDNAME = '__url';
    const TITLE_FIELDNAME = '__title';
    const DESCRIPTION_FIELDNAME = '__description';

    protected $basePath;

    /**
     * @param string $basePath Base filesystem path for the index
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Determine the index path for a given index name
     * @param string $indexName
     * @return string
     */
    protected function getIndexPath($indexName)
    {
        return sprintf('%s/%s', $this->basePath, $indexName);
    }

    /**
     * {@inheritDoc}
     */
    public function index(Document $document, $indexName)
    {
        $indexPath = $this->getIndexPath($indexName);

        if (!file_exists($indexPath)) {
            $index = Lucene\Lucene::create($indexPath);
        } else {
            $index = Lucene\Lucene::open($indexPath);

            // check to see if the subject already exists
            $this->removeExisting($index, $document);
        }

        $luceneDocument = new Lucene\Document();

        foreach ($document->getFields() as $field) {
            switch ($field->getType()) {
                case Field::TYPE_STRING:
                default:
                    $luceneDocument->addField(Lucene\Document\Field::Text($field->getName(), $field->getValue()));
                    break;
            }
        }

        // add meta fields - used internally for showing the search results, etc.
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::ID_FIELDNAME, $document->getId()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::URL_FIELDNAME, $document->getUrl()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::TITLE_FIELDNAME, $document->getTitle()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::DESCRIPTION_FIELDNAME, $document->getDescription()));

        $index->addDocument($luceneDocument);
    }

    /**
     * Remove the existing entry for the given Document from the index, if it exists.
     *
     * @param Lucene\Index $index The Zend Lucene Index
     * @param Document $document The Massive Search Document
     */
    protected function removeExisting(Lucene\Index $index, Document $document)
    {
        $hits = $index->find(self::ID_FIELDNAME . ':' . $document->getId());

        foreach ($hits as $hit) {
            $index->delete($hit->id);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function search($queryString, array $indexNames = array())
    {
        $searcher = new Lucene\MultiSearcher();

        foreach ($indexNames as $indexName) {
            $searcher->addIndex(Lucene\Lucene::open($this->getIndexPath($indexName)));
        }

        $query = Lucene\Search\QueryParser::parse($queryString);

        $luceneHits = $searcher->find($query);

        $hits = array();

        foreach ($luceneHits as $luceneHit) {
            $hit = new QueryHit();
            $document = new Document();
            $hit->setDocument($document);
            $hit->setScore($luceneHit->score);

            $luceneDocument = $luceneHit->getDocument();

            // map meta fields to document
            $document->setId($luceneDocument->getFieldValue(self::ID_FIELDNAME));
            $document->setTitle($luceneDocument->getFieldValue(self::TITLE_FIELDNAME));
            $document->setDescription($luceneDocument->getFieldValue(self::DESCRIPTION_FIELDNAME));
            $document->setUrl($luceneDocument->getFieldValue(self::URL_FIELDNAME));

            $hit->setId($document->getId());

            foreach ($luceneDocument->getFieldNames() as $fieldName) {
                $document->addField(Field::create($fieldName, $luceneDocument->getFieldValue($fieldName)));
            }
            $hits[] = $hit;
        }

        return $hits;
    }
}
