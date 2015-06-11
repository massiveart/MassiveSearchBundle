<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Benchmark;

use Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use PhpBench\Benchmark;
use PhpBench\Benchmark\Iteration;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;

abstract class AdapterBench extends BaseTestCase implements Benchmark
{
    public function setUp()
    {
        AppKernel::resetEnvironment();
        AppKernel::installDistEnvironment();
    }

    public function tearDown()
    {
    }

    protected abstract function getAdapterId();

    protected static function getKernelClass()
    {
        return 'Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel';
    }

    /**
     * @description Indexing
     * @paramProvider provideNbDocuments
     * @group index
     */
    public function benchIndex(Iteration $iteration)
    {
        $manager = $this->getSearchManager($this->getAdapterId());
        $manager->purge('product');

        $nbDocuments = $iteration->getParameter('nb_documents');

        for ($i = 0; $i < $nbDocuments; $i++) {
            $product = new Product();
            $product->setId($i);

            if ($i == floor($nbDocuments / 2)) {
                $product->setTitle('Needle');
            } {
                $product->setTitle('Hello this is a product '.$i);
            }
            $product->setBody('To be or not to be, that is the question');
            $product->setUrl('/foobar');
            $product->setLocale('fr');

            $manager->index($product);
        }
    }

    /**
     * @description Search
     * @paramProvider provideNbDocuments
     * @beforeMethod benchIndex
     * @group search
     */
    public function benchSearch()
    {
        $manager = $this->getSearchManager($this->getAdapterId());
        $this->lastResult = $manager->createSearch('Needle')->index('product')->locale('fr')->execute();
    }

    public function getSearchManager($adapterId)
    {
        return new SearchManager(
            $this->getContainer()->get($adapterId),
            $this->getContainer()->get('massive_search.metadata.provider.chain'),
            $this->getContainer()->get('massive_search.object_to_document_converter'),
            $this->getContainer()->get('event_dispatcher'),
            $this->getContainer()->get('massive_search.localization_strategy')
        );
    }

    public function provideNbDocuments()
    {
        return array(
            array(
                'nb_documents' => 10,
            ),
            array(
                'nb_documents' => 100,
            ),
            array(
                'nb_documents' => 1000,
            ),
        );
    }
}
