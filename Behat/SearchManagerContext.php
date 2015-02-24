<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Behat;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPUnit_Framework_Assert as Assert;
use Massive\Bundle\SearchBundle\Search\SearchManager;

/**
 * Behat context for search manager features
 */
class SearchManagerContext implements SnippetAcceptingContext, KernelAwareContext
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
    private $entities = array();

    /**
     * @param string $adapterId
     */
    public function __construct($adapterId)
    {
        $this->adapterId = $adapterId;
    }

    /**
     * @BeforeSuite
     */
    public static function clearCache()
    {
        AppKernel::clearData();
    }

    /**
     * @BeforeScenario
     */
    public function setUp()
    {
        $this->kernel->shutdown();
        $this->kernel->boot();
        AppKernel::resetEnvironment();
    }

    /**
     * {@inheritDoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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
        Assert::assertEquals($expected, $this->lastResult);
    }

    /**
     * @Given the following ":className" objects have been persisted
     */
    public function iIndexTheFollowingObjects($className, PyStringNode $string)
    {
        $objectsData = json_decode($string->getRaw(), true);
        Assert::assertArrayHasKey($className, $this->entityClasses);
        Assert::assertNotNull($objectsData);

        foreach ($objectsData as $objectData) {
            $object = new $this->entityClasses[$className]();
            foreach ($objectData as $key => $value) {
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
        $this->lastResult = $this->getSearchManager()->createSearch($query)->execute();
    }

    /**
     * @Given I search for :query in locale :locale
     */
    public function iSearchForInLocale($query, $locale)
    {
        $this->lastResult = $this->getSearchManager()->createSearch($query)->locale($locale)->execute();
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
        Assert::assertCount((integer) $nbResults, $this->lastResult);
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
        Assert::arrayHasKey($id, $this->entities);
        $entity = $this->entities[$id];
        $this->getSearchManager()->deindex($entity);
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
        Assert::assertInternalType('array', $this->lastResult);
    }

    /**
     * Return the search manager using the configured adapter ID
     */
    protected function getSearchManager()
    {
        return new SearchManager(
            $this->kernel->getContainer()->get($this->adapterId),
            $this->kernel->getContainer()->get('massive_search.metadata.factory'),
            $this->kernel->getContainer()->get('massive_search.object_to_document_converter'),
            $this->kernel->getContainer()->get('event_dispatcher'),
            $this->kernel->getContainer()->get('massive_search.localization_strategy')
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
