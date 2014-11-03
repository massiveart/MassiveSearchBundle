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
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Massive\Bundle\SearchBundle\Search\Adapter\Zend\Index;
use ZendSearch\Lucene;

/**
 * Adapter for the ZendSearch library
 *
 * https://github.com/zendframework/ZendSearch
 * http://framework.zend.com/manual/1.12/en/zend.search.lucene.html 
 *   (docs for 1.2 version apply equally to 2.0)
 *
 * Note this adapter implements localization by creating an index for each
 * locale if a locale is specified.
 *
 * @author Daniel Leech <daniel@massive.com>
 */
class ZendLuceneAdapter implements AdapterInterface
{
    const ID_FIELDNAME = '__id';
    const CLASS_TAG = '__class';

    // TODO: This fields should be handled at a higher level
    const URL_FIELDNAME = '__url';
    const TITLE_FIELDNAME = '__title';
    const DESCRIPTION_FIELDNAME = '__description';
    const IMAGE_URL = '__image_url';

    /**
     * The base directory for the search indexes
     * @var string
     */
    protected $basePath;

    /**
     * @var \Massive\Bundle\SearchBundle\Search\Factory
     */
    protected $factory;
    protected $hideIndexException;

    /**
     * @param string $basePath Base filesystem path for the index
     */
    public function __construct(Factory $factory, $basePath, $hideIndexException = false)
    {
        $this->basePath = $basePath;
        $this->factory = $factory;
        $this->hideIndexException = $hideIndexException;
    }

    /**
     * {@inheritDoc}
     */
    public function index(Document $document, $indexName)
    {
        $index = $this->getLuceneIndex($document, $indexName);

        // check to see if the subject already exists
        $this->removeExisting($index, $document);

        $luceneDocument = new Lucene\Document();

        foreach ($document->getFields() as $field) {
            switch ($field->getType()) {
                case Field::TYPE_STRING:
                    $luceneField = Lucene\Document\Field::Text($field->getName(), $field->getValue());
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Search field type "%s" is not know. Known types are: %s',
                        implode(', ', Field::getValidTypes())
                    ));
            }

            $luceneDocument->addField($luceneField);
        }

        // add meta fields - used internally for showing the search results, etc.
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::ID_FIELDNAME, $document->getId()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::URL_FIELDNAME, $document->getUrl()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::TITLE_FIELDNAME, $document->getTitle()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::DESCRIPTION_FIELDNAME, $document->getDescription()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::CLASS_TAG, $document->getClass()));
        $luceneDocument->addField(Lucene\Document\Field::Keyword(self::IMAGE_URL, $document->getImageUrl()));

        $index->addDocument($luceneDocument);
    }

    /**
     * {@inheritDoc}
     */
    public function deindex(Document $document, $indexName)
    {
        $index = $this->getLuceneIndex($document, $indexName);
        $this->removeExisting($index, $document);
    }

    /**
     * {@inheritDoc}
     */
    public function search(SearchQuery $searchQuery)
    {
        $indexNames = $searchQuery->getIndexes();
        $locale = $searchQuery->getLocale();
        $queryString = $searchQuery->getQueryString();

        $searcher = new Lucene\MultiSearcher();

        foreach ($indexNames as $indexName) {
            $indexPath = $this->getIndexPath(
                $this->getLocalizedIndexName($indexName, $locale)
            );

            if (!file_exists($indexPath)) {
                continue;
            }

            $searcher->addIndex($this->getIndex($indexPath, false));
        }

        $query = Lucene\Search\QueryParser::parse($queryString);

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

            $index = $this->getIndex($this->getIndexPath($indexName, false));

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

    /**
     * Returns the lucene index for the locale determined by the document
     * @param Document $document
     * @param $indexName
     * @return Lucene\SearchIndexInterface
     */
    private function getLuceneIndex(Document $document, $indexName)
    {
        $locale = $document->getLocale();
        $indexName = $this->getLocalizedIndexName($indexName, $locale);
        $indexPath = $this->getIndexPath($indexName);

        if (!file_exists($indexPath)) {
             $this->getIndex($indexPath, true);
        }

        return $this->getIndex($indexPath, false);
    }

    /**
     * Determine the index path for a given index name
     * @param string $indexName
     * @return string
     */
    private function getIndexPath($indexName)
    {
        return sprintf('%s/%s', $this->basePath, $indexName);
    }

    /**
     * Return the localized index name if the locale
     * is given.
     *
     * @return string
     */
    private function getLocalizedIndexName($indexName, $locale = null)
    {
        if (null === $locale) {
            return $indexName;
        }

        return $indexName . '_' . $locale;
    }

    /**
     * Remove the existing entry for the given Document from the index, if it exists.
     *
     * @param Lucene\Index $index The Zend Lucene Index
     * @param Document $document The Massive Search Document
     */
    private function removeExisting(Index $index, Document $document)
    {
        $hits = $index->find(self::ID_FIELDNAME . ':' . $document->getId());

        foreach ($hits as $hit) {
            $index->delete($hit->id);
        }
    }

    /**
     * Return the index. Note that we override the default ZendSeach index
     * to allow us to catch the exception thrown during __destruct when running
     * functional tests.
     *
     * @param string $indexPath
     * @param boolean $create Create an index or open it
     */
    private function getIndex($indexPath, $create = false)
    {
        $index = new Index($indexPath, $create);
        $index->setHideException($this->hideIndexException);

        return $index;
    }
}
