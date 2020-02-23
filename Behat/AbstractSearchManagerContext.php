<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel;
use Webmozart\Assert\Assert;

/**
 * Behat context for search manager features.
 */
abstract class AbstractSearchManagerContext implements Context
{
    /**
     * @var string
     */
    private $adapterId;

    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @var mixed
     */
    private $lastResult;

    /**
     * @var string[]
     */
    private $entityClasses;

    /**
     * @var object[]
     */
    private $entities = [];

    /**
     * @var \Exception
     */
    private $lastException = null;

    /**
     * @var bool
     */
    private $exceptionAsserted = false;

    /**
     * @param string $adapterId
     */
    public function __construct($adapterId)
    {
        $this->adapterId = $adapterId;
        require_once __DIR__ . '/../vendor/symfony-cmf/testing/bootstrap/bootstrap.php';
        $this->kernel = new AppKernel('test', true);
    }

    /**
     * @BeforeScenario
     */
    public function setUp()
    {
        $this->kernel->shutdown();
        $this->kernel->boot();
        AppKernel::resetEnvironment();
        AppKernel::clearData();

        // clear indexes
        $indexNames = $this->getSearchManager()->getIndexNames();
        foreach ($indexNames as $indexName) {
            $this->getSearchManager()->purge($indexName);
        }
    }

    /**
     * @Given that the following mapping for :mappingName exists:
     */
    public function thatTheFollowingMappingExists($mappingName, PyStringNode $mappingXml)
    {
        file_put_contents(AppKernel::getMappingDir() . '/' . $mappingName . '.xml', $mappingXml->getRaw());
    }

    /**
     * @Given the entity ":name" exists:
     */
    public function theFollowingEntityExists($name, PyStringNode $string)
    {
        $this->entityClasses[$name] = 'Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\\' . $name;
        file_put_contents(AppKernel::getEntityDir() . '/' . $name . '.php', $string->getRaw());
        $this->pause();
    }

    /**
     * @Given I get the index names
     */
    public function iGetTheIndexNames()
    {
        $this->getSearchManager()->flush();
        $this->pause();
        $this->lastResult = $this->getSearchManager()->getIndexNames();
    }

    /**
     * @Then the result should be the following array:
     */
    public function theResultShouldBeTheFollowingArray(PyStringNode $string)
    {
        $expected = json_decode($string->getRaw(), true);
        Assert::eq($expected, $this->lastResult);
    }

    /**
     * @Then I should have the following documents:
     */
    public function iShouldHaveTheFollowingDocuments(PyStringNode $string)
    {
        $expected = json_decode($string->getRaw(), true);
        $documents = [];
        foreach ($this->lastResult as $hit) {
            $documents[] = $hit->getDocument()->jsonSerialize();
        }
        Assert::eq($expected, $documents);
    }

    /**
     * @When I index the following ":className" objects
     */
    public function whenIIndexTheFollowingObjects($className, PyStringNode $string)
    {
        try {
            $this->doIndexTheFollowingObjects($className, $string);
        } catch (\Exception $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @Given the following ":className" objects have been indexed
     */
    public function givenIIndexTheFollowingObjects($className, PyStringNode $string)
    {
        $this->doIndexTheFollowingObjects($className, $string);
    }

    private function doIndexTheFollowingObjects($className, PyStringNode $string)
    {
        $objectsData = json_decode($string->getRaw(), true);
        Assert::keyExists($this->entityClasses, $className, 'Entity exists');
        Assert::notNull($objectsData);

        foreach ($objectsData as $objectData) {
            $object = new $this->entityClasses[$className]();
            foreach ($objectData as $key => $value) {
                if (is_string($value) && false !== ($date = \DateTime::createFromFormat('Y-m-d', $value))) {
                    $value = $date;
                }
                $object->$key = $value;
            }
            $this->entities[$object->id] = $object;
            $this->getSearchManager()->index($object);
        }

        $this->getSearchManager()->flush();

        $this->pause();
    }

    /**
     * @Given I search for :query
     */
    public function iSearchFor($query)
    {
        $this->lastResult = $this->getSearchManager()
            ->createSearch($query)
            ->indexes($this->getSearchManager()->getIndexNames())
            ->execute();
    }

    /**
     * @When I search for :query with sort :sort and order :order
     */
    public function iSearchForWithSort($query, $sort, $order)
    {
        $this->lastResult = $this->getSearchManager()
            ->createSearch($query)
            ->indexes($this->getSearchManager()->getIndexNames())
            ->addSorting($sort, $order)
            ->execute();
    }

    /**
     * @When I search for :query with limit :limit and offset :offset
     */
    public function iSearchForWithLimitAndOffset($query, $limit, $offset)
    {
        $this->lastResult = $this->getSearchManager()
            ->createSearch($query)
            ->indexes($this->getSearchManager()->getIndexNames())
            ->setLimit(intval($limit))
            ->setOffset(intval($offset))
            ->execute();
    }

    /**
     * @Given I search for :query in locale :locale
     */
    public function iSearchForInLocale($query, $locale)
    {
        $this->lastResult = $this->getSearchManager()
            ->createSearch($query)
            ->indexes($this->getSearchManager()->getIndexNames())
            ->locale($locale)
            ->execute();
    }

    /**
     * @When I search for :query in index :index
     */
    public function iSearchForInIndex($query, $index)
    {
        try {
            $this->lastResult = $this->getSearchManager()->createSearch($query)->index($index)->execute();
        } catch (\Exception $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @Then an exception with message :message should be thrown
     */
    public function thenAnExceptionWithMessageShouldBeThrown($message)
    {
        Assert::notNull($this->lastException, 'An exception has been thrown');
        Assert::contains($message, $this->lastException->getMessage());
        $this->exceptionAsserted = true;
    }

    /**
     * @Given I search for :query in locale :locale with index :index
     */
    public function iSearchForInLocaleForIndex($query, $locale, $index)
    {
        $this->lastResult = $this->getSearchManager()->createSearch($query)->index($index)->locale($locale)->execute();
    }

    /**
     * @Then there should be :nbResults results
     */
    public function thereShouldBeResults($nbResults)
    {
        Assert::count($this->lastResult, (int) $nbResults);
    }

    /**
     * @Given I purge the index :indexName
     */
    public function iPurgeTheIndex($indexName)
    {
        $this->getSearchManager()->purge($indexName);
        $this->pause();
    }

    /**
     * @Given I deindex the object with id :id
     */
    public function iDeindexTheObjectWithId($id)
    {
        Assert::keyExists($this->entities, $id);
        $entity = $this->entities[$id];
        $this->getSearchManager()->deindex($entity);
        $this->getSearchManager()->flush();
        $this->pause();
    }

    /**
     * @Given I deindex a not existing ":className" object with id :id
     */
    public function iDeindexNotExistingObjectWithId($className, $id)
    {
        $object = new $this->entityClasses[$className]();
        $object->id = $id;

        $this->getSearchManager()->deindex($object);
        $this->getSearchManager()->flush();
        $this->pause();
    }

    /**
     * @Given I get the status
     */
    public function iGetTheStatus()
    {
        $this->lastResult = $this->getSearchManager()->getStatus();
    }

    /**
     * @Then the result should be an array
     */
    public function theResultShouldBeAnArray()
    {
        Assert::isArray($this->lastResult);
    }

    /**
     * @Then the result at position :position should be :id
     */
    public function theResultAtPositionShouldBe($position, $id)
    {
        Assert::eq($this->lastResult[$position]->getId(), $id);
    }

    /**
     * Return the search manager using the configured adapter ID.
     */
    protected function getSearchManager()
    {
        return new SearchManager(
            $this->kernel->getContainer()->get($this->adapterId),
            $this->kernel->getContainer()->get('massive_search_test.metadata.provider.chain'),
            $this->kernel->getContainer()->get('massive_search_test.object_to_document_converter'),
            $this->kernel->getContainer()->get('event_dispatcher'),
            $this->kernel->getContainer()->get('massive_search_test.index_name_decorator.default'),
            $this->kernel->getContainer()->get('massive_search_test.metadata.field_evaluator')
        );
    }

    /**
     * There is a timing issue, we need to pause for a while
     * after flusing for subsequent requests to be consistent.
     */
    protected function pause()
    {
        usleep(50000);
    }
}
