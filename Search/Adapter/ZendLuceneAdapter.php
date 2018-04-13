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

use Massive\Bundle\SearchBundle\Search\Adapter\Zend\Index;
use Massive\Bundle\SearchBundle\Search\AdapterInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Field;
use Massive\Bundle\SearchBundle\Search\QueryHit;
use Massive\Bundle\SearchBundle\Search\SearchQuery;
use Massive\Bundle\SearchBundle\Search\SearchResult;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZendSearch\Lucene;

/**
 * Adapter for the ZendSearch library.
 *
 * https://github.com/zendframework/ZendSearch
 * http://framework.zend.com/manual/1.12/en/zend.search.lucene.html
 *   (docs for 1.2 version apply equally to 2.0)
 */
class ZendLuceneAdapter implements AdapterInterface
{
    const ID_FIELDNAME = '__id';

    const INDEX_FIELDNAME = '__index';

    const CLASS_TAG = '__class';

    const AGGREGATED_INDEXED_CONTENT = '__content';

    const URL_FIELDNAME = '__url';

    const TITLE_FIELDNAME = '__title';

    const LOCALE_FIELDNAME = '__locale';

    const DESCRIPTION_FIELDNAME = '__description';

    const IMAGE_URL = '__image_url';

    /**
     * @var \Massive\Bundle\SearchBundle\Search\Factory
     */
    private $factory;

    /**
     * The base directory for the search indexes.
     *
     * @var string
     */
    private $basePath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var bool
     */
    private $hideIndexException;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @param Factory $factory
     * @param string $basePath Base filesystem path for the index
     * @param Filesystem $filesystem
     * @param bool $hideIndexException
     * @param null $encoding
     */
    public function __construct(
        Factory $factory,
        $basePath,
        Filesystem $filesystem,
        $hideIndexException = false,
        $encoding = null
    ) {
        $this->factory = $factory;
        $this->basePath = $basePath;
        $this->filesystem = $filesystem;
        $this->hideIndexException = $hideIndexException;
        $this->encoding = $encoding;

        Lucene\Search\QueryParser::setDefaultEncoding($this->encoding);
        Lucene\Search\QueryParser::setDefaultOperator(Lucene\Search\QueryParser::B_AND);
        Lucene\Analysis\Analyzer\Analyzer::setDefault(
            new Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function index(Document $document, $indexName)
    {
        $index = $this->getLuceneIndex($indexName);

        // check to see if the subject already exists
        $this->removeExisting($index, $document);

        $luceneDocument = new Lucene\Document();

        $aggregateValues = [];
        foreach ($document->getFields() as $field) {
            $type = $field->getType();
            $value = $field->getValue();

            if (Field::TYPE_NULL === $type) {
                continue;
            }

            // Zend Lucene does not support "types". We should allow other "types" once they
            // are properly implemented in at least one other adapter.
            if (Field::TYPE_STRING !== $type && Field::TYPE_ARRAY !== $type) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Search field type "%s" is not known. Known types are: %s',
                        $field->getType(),
                        implode('", "', Field::getValidTypes())
                    )
                );
            }

            // handle array values
            if (is_array($value)) {
                $value = '|' . implode('|', $value) . '|';
            }

            $luceneFieldType = $this->getFieldType($field);
            $luceneField = Lucene\Document\Field::$luceneFieldType(
                $field->getName(),
                $value,
                $this->encoding
            );

            if ($field->isAggregate()) {
                $aggregateValues[] = $value;
            }

            $luceneDocument->addField($luceneField);
        }

        // add meta fields - used internally for showing the search results, etc.
        $luceneDocument->addField(Lucene\Document\Field::keyword(self::ID_FIELDNAME, $document->getId()));
        $luceneDocument->addField(Lucene\Document\Field::keyword(self::INDEX_FIELDNAME, $document->getIndex()));
        $luceneDocument->addField(
            Lucene\Document\Field::unStored(self::AGGREGATED_INDEXED_CONTENT, implode(' ', $aggregateValues))
        );
        $luceneDocument->addField(Lucene\Document\Field::unIndexed(self::URL_FIELDNAME, $document->getUrl()));
        $luceneDocument->addField(Lucene\Document\Field::unIndexed(self::TITLE_FIELDNAME, $document->getTitle()));
        $luceneDocument->addField(
            Lucene\Document\Field::unIndexed(self::DESCRIPTION_FIELDNAME, $document->getDescription())
        );
        $luceneDocument->addField(Lucene\Document\Field::unIndexed(self::LOCALE_FIELDNAME, $document->getLocale()));
        $luceneDocument->addField(Lucene\Document\Field::unIndexed(self::CLASS_TAG, $document->getClass()));
        $luceneDocument->addField(Lucene\Document\Field::unIndexed(self::IMAGE_URL, $document->getImageUrl()));

        $index->addDocument($luceneDocument);

        return $luceneDocument;
    }

    /**
     * {@inheritdoc}
     */
    public function deindex(Document $document, $indexName)
    {
        $index = $this->getLuceneIndex($indexName);
        $this->removeExisting($index, $document);
        $index->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchQuery $searchQuery)
    {
        $indexNames = $searchQuery->getIndexes();
        $queryString = $searchQuery->getQueryString();

        $searcher = new Lucene\MultiSearcher();

        foreach ($indexNames as $indexName) {
            $indexPath = $this->getIndexPath($indexName);
            if (!file_exists($indexPath)) {
                continue;
            }

            $searcher->addIndex($this->getIndex($indexPath, false));
        }

        $query = Lucene\Search\QueryParser::parse($queryString);

        try {
            $luceneHits = $searcher->find($query);
        } catch (\RuntimeException $e) {
            if (!preg_match('&non-wildcard characters&', $e->getMessage())) {
                throw $e;
            }

            $luceneHits = [];
        }

        $endPos = count($luceneHits);
        $startPos = $searchQuery->getOffset();

        if (null !== $searchQuery->getLimit()) {
            $endPos = min(count($luceneHits), $startPos + $searchQuery->getLimit());
        }

        $hits = [];
        for ($pos = $startPos; $pos < $endPos; ++$pos) {
            /* @var Lucene\Search\QueryHit $luceneHit */

            $luceneHit = $luceneHits[$pos];
            $luceneDocument = $luceneHit->getDocument();

            $hit = $this->factory->createQueryHit();
            $document = $this->factory->createDocument();

            $hit->setDocument($document);
            $hit->setScore($luceneHit->score);

            // map meta fields to document "product"
            $document->setId($luceneDocument->getFieldValue(self::ID_FIELDNAME));
            $document->setIndex($luceneDocument->getFieldValue(self::INDEX_FIELDNAME));
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
        usort(
            $hits,
            function (QueryHit $documentA, QueryHit $documentB) {
                if ($documentA->getScore() < $documentB->getScore()) {
                    return true;
                }

                return false;
            }
        );

        return new SearchResult($hits, count($luceneHits));
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        $finder = new Finder();
        $indexDirs = $finder->directories()->depth('== 0')->in($this->basePath);
        $status = [];

        foreach ($indexDirs as $indexDir) {
            /* @var  $indexDir \Symfony\Component\Finder\SplFileInfo; */

            $indexFinder = new Finder();
            $files = $indexFinder->files()->name('*')->depth('== 0')->in($indexDir->getPathname());
            $indexName = basename($indexDir);

            $index = $this->getIndex($this->getIndexPath($indexName));

            $indexStats = [
                'size' => 0,
                'nb_files' => 0,
                'nb_documents' => $index->count(),
            ];

            foreach ($files as $file) {
                $indexStats['size'] += filesize($file);
                ++$indexStats['nb_files'];
            }

            $status['idx:' . $indexName] = json_encode($indexStats);
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($indexName)
    {
        $indexPath = $this->getIndexPath($indexName);
        $fs = new Filesystem();
        $fs->remove($indexPath);
    }

    /**
     * {@inheritdoc}
     */
    public function listIndexes()
    {
        if (!file_exists($this->basePath)) {
            return [];
        }

        $finder = new Finder();
        $indexDirs = $finder->directories()->depth('== 0')->in($this->basePath);
        $names = [];

        foreach ($indexDirs as $file) {
            /* @var  $file \Symfony\Component\Finder\SplFileInfo; */
            $names[] = $file->getBasename();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(array $indexNames)
    {
    }

    /**
     * Optimizes the index with the given name (that's zend lucene specific).
     *
     * @param string $indexName
     */
    public function optimize($indexName)
    {
        $index = $this->getLuceneIndex($indexName);
        $index->optimize();
    }

    /**
     * Return (or create) a Lucene index for the given name.
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
     * Determine the index path for a given index name.
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
     * @param bool $create Create an index or open it
     *
     * @return Index
     */
    private function getIndex($indexPath, $create = false)
    {
        $index = new Index($indexPath, $create);
        $index->setHideException($this->hideIndexException);

        return $index;
    }

    /**
     * Return the zend lucene field type to use for the given field.
     *
     * @param Field $field
     *
     * @return string
     */
    private function getFieldType(Field $field)
    {
        if ($field->isStored() && $field->isIndexed()) {
            return 'text';
        }

        if (false === $field->isStored() && $field->isIndexed()) {
            return 'unStored';
        }

        if ($field->isStored() && false === $field->isIndexed()) {
            return 'unIndexed';
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Field "%s" cannot be both not indexed and not stored',
                $field->getName()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (!$this->filesystem->exists($this->basePath)) {
            $this->filesystem->mkdir($this->basePath);
        }
    }
}
