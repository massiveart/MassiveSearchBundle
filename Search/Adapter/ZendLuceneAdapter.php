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
use Massive\Bundle\SearchBundle\Search\Adapter\Zend\Index;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Event\IndexRebuildEvent;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\QueryHit;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use ZendSearch\Zend_Search_Lucene;

/**
 * Adapter for the ZendSearch library
 *
 * https://github.com/zendframework/ZendSearch
 * http://framework.zend.com/manual/1.12/en/zend.search.lucene.html
 *   (docs for 1.2 version apply equally to 2.0)
 */
class ZendLuceneAdapter implements AdapterInterface
{
    const ID_FIELDNAME = '__id';
    const CLASS_TAG = '__class';
    const AGGREGATED_INDEXED_CONTENT = '__content';

    const URL_FIELDNAME = '__url';
    const TITLE_FIELDNAME = '__title';
    const LOCALE_FIELDNAME = '__locale';
    const DESCRIPTION_FIELDNAME = '__description';
    const IMAGE_URL = '__image_url';

    /**
     * The base directory for the search indexes
     * @var string
     */
    private $basePath;

    /**
     * @var \Massive\Bundle\SearchBundle\Search\Factory
     */
    private $factory;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var string
     */
    private $defaultIndexStrategy;

    /**
     * @param Factory $factory
     * @param string $basePath Base filesystem path for the index
     * @param null $encoding
     * @param string $defaultIndexStrategy
     */
    public function __construct(
        Factory $factory,
        $basePath,
        $encoding = null,
        $defaultIndexStrategy = Field::INDEX_AGGREGATE
    )
    {
        $this->basePath = $basePath;
        $this->factory = $factory;
        $this->encoding = $encoding;
        $this->defaultIndexStrategy = $defaultIndexStrategy;

        \Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding($this->encoding);
        \Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(\Zend_Search_Lucene_Search_QueryParser::B_AND);
        \Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function index(Document $document, $indexName)
    {
        $index = $this->getLuceneIndex($indexName);

        // check to see if the subject already exists
        $this->removeExisting($index, $document);

        $luceneDocument = new \Zend_Search_Lucene_Document();

        $values = array();
        foreach ($document->getFields() as $field) {
            // Zend Lucene does not support "types". We should allow other "types" once they
            // are properly implemented in at least one other adapter.
            if ($field->getType() !== Field::TYPE_STRING) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Search field type "%s" is not known. Known types are: %s',
                        $field->getType(),
                        implode('", "', Field::getValidTypes())
                    )
                );
            }

            $indexStrategy = $field->getIndexStrategy() ?: $this->defaultIndexStrategy;

            switch ($indexStrategy) {
                case Field::INDEX_AGGREGATE:
                    $luceneField = \Zend_Search_Lucene_Field::unIndexed(
                        $field->getName(),
                        $field->getValue(),
                        $this->encoding
                    );
                    $values[] = $field->getValue();
                    break;
                case Field::INDEX_UNSTORED:
                    $luceneField = \Zend_Search_Lucene_Field::unStored(
                        $field->getName(),
                        $field->getValue(),
                        $this->encoding
                    );
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Unknown index strategy "%s", must be one of "%s"',
                            $field->getIndexStrategy(),
                            implode('", "', array(Field::INDEX_AGGREGATE, Field::INDEX_UNSTORED,))
                        )
                    );
            }

            $luceneDocument->addField($luceneField);
        }

        // add meta fields - used internally for showing the search results, etc.
        $luceneDocument->addField(\Zend_Search_Lucene_Field::keyword(self::ID_FIELDNAME, $document->getId()));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unStored(self::AGGREGATED_INDEXED_CONTENT, implode(' ', $values)));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unIndexed(self::URL_FIELDNAME, $document->getUrl()));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unIndexed(self::TITLE_FIELDNAME, $document->getTitle()));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unIndexed(self::DESCRIPTION_FIELDNAME, $document->getDescription()));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unIndexed(self::LOCALE_FIELDNAME, $document->getLocale()));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unIndexed(self::CLASS_TAG, $document->getClass()));
        $luceneDocument->addField(\Zend_Search_Lucene_Field::unIndexed(self::IMAGE_URL, $document->getImageUrl()));

        $index->addDocument($luceneDocument);
    }

    /**
     * {@inheritDoc}
     */
    public function deindex(Document $document, $indexName)
    {
        $index = $this->getLuceneIndex($indexName);
        $this->removeExisting($index, $document);
        $index->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function search(SearchQuery $searchQuery)
    {
        $indexNames = $searchQuery->getIndexes();
        $queryString = $searchQuery->getQueryString();

        $searcher = new Zend\MultiSearcher();

        foreach ($indexNames as $indexName) {
            $indexPath = $this->getIndexPath($indexName);
            if (!file_exists($indexPath)) {
                continue;
            }

            $searcher->addIndex($this->getIndex($indexPath, false));
        }

        $query = \Zend_Search_Lucene_Search_QueryParser::parse($queryString);

        try {
            $luceneHits = $searcher->find($query);
        } catch (\RuntimeException $e) {
            if (!preg_match('&non-wildcard characters&', $e->getMessage())) {
                throw $e;
            }

            $luceneHits = array();
        }

        $hits = array();

        foreach ($luceneHits as $luceneHit) {
            /** @var \Zend_Search_Lucene_Search_QueryHit $luceneHit */

            $luceneDocument = $luceneHit->getDocument();

            $hit = $this->factory->createQueryHit();
            $document = $this->factory->createDocument();

            $hit->setDocument($document);
            $hit->setScore($luceneHit->score);

            // map meta fields to document "product"
            $document->setId($luceneDocument->getFieldValue(self::ID_FIELDNAME));
            $document->setTitle($luceneDocument->getFieldValue(self::TITLE_FIELDNAME));
            $document->setDescription($luceneDocument->getFieldValue(self::DESCRIPTION_FIELDNAME));
            $document->setLocale($luceneDocument->getFieldValue(self::LOCALE_FIELDNAME));
            $document->setUrl($luceneDocument->getFieldValue(self::URL_FIELDNAME));
            $document->setClass($luceneDocument->getFieldValue(self::CLASS_TAG));
            $document->setImageUrl($luceneDocument->getFieldValue(self::IMAGE_URL));

            $hit->setId($document->getId());

            foreach ($luceneDocument->getFieldNames() as $fieldName) {
                $document->addField(
                    $this->factory->createField($fieldName, $luceneDocument->getFieldValue($fieldName))
                );
            }
            $hits[] = $hit;
        }

        // The MultiSearcher does not support sorting, so we do it here.
        usort($hits, function (QueryHit $documentA, QueryHit $documentB) {
            if ($documentA->getScore() < $documentB->getScore()) {
                return true;
            }
            return false;
        });

        return $hits;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        $finder = new Finder();
        $indexDirs = $finder->directories()->depth('== 0')->in($this->basePath);
        $status = array();

        foreach ($indexDirs as $indexDir) {
            /** @var  $indexDir \Symfony\Component\Finder\SplFileInfo; */

            $indexFinder = new Finder();
            $files = $indexFinder->files()->name('*')->depth('== 0')->in($indexDir->getPathname());
            $indexName = basename($indexDir);

            $index = $this->getIndex($this->getIndexPath($indexName));

            $indexStats = array(
                'size' => 0,
                'nb_files' => 0,
                'nb_documents' => $index->count(),
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
     * {@inheritDoc}
     */
    public function purge($indexName)
    {
        $indexPath = $this->getIndexPath($indexName);
        $fs = new Filesystem();
        $fs->remove($indexPath);
    }

    /**
     * {@inheritDoc}
     */
    public function listIndexes()
    {
        if (!file_exists($this->basePath)) {
            return array();
        }

        $finder = new Finder();
        $indexDirs = $finder->directories()->depth('== 0')->in($this->basePath);
        $names = array();

        foreach ($indexDirs as $file) {
            /** @var  $file \Symfony\Component\Finder\SplFileInfo; */
            $names[] = $file->getBasename();
        }

        return $names;
    }

    /**
     * {@inheritDoc}
     */
    public function flush(array $indexNames)
    {
    }

    /**
     * Return (or create) a Lucene index for the given name
     *
     * @param string $indexName
     *
     * @return Index
     */
    private function getLuceneIndex($indexName)
    {
        $indexPath = $this->getIndexPath($indexName);

        if (!file_exists($indexPath)) {
            $this->getIndex($indexPath, true);
        }

        return $this->getIndex($indexPath, false);
    }

    /**
     * Determine the index path for a given index name
     *
     * @param string $indexName
     *
     * @return string
     */
    private function getIndexPath($indexName)
    {
        return sprintf('%s/%s', $this->basePath, $indexName);
    }

    /**
     * Remove the existing entry for the given Document from the index, if it exists.
     *
     * @param Index $index The Zend Lucene Index
     * @param Document $document The Massive Search Document
     */
    private function removeExisting(\Zend_Search_Lucene_Interface $index, Document $document)
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
     * @param bool $create Create an index or open it
     *
     * @return Index
     */
    private function getIndex($indexPath, $create = false)
    {
        if ($create) {
            return \Zend_Search_Lucene::create($indexPath);
        }

        return \Zend_Search_Lucene::open($indexPath);
    }

    /**
     * Optimize the search indexes after the index rebuild event has been fired.
     * Should have a priority low enough in order for it to be executed after all
     * the actual index builders.
     *
     * @param IndexRebuildEvent $event
     */
    public function optimizeIndexAfterRebuild(IndexRebuildEvent $event)
    {
        foreach ($this->listIndexes() as $indexName) {
            $event->getOutput()->writeln(sprintf('<info>Optimizing zend lucene index:</info> %s', $indexName));
            $index = $this->getLuceneIndex($indexName);
            $index->optimize();
        }
    }
}
