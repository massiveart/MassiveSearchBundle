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

use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

/**
 * @Iterations(3)
 * @BeforeMethods({"setUp"})
 * @BeforeMethods({"tearDown"})
 */
abstract class AdapterBench extends BaseTestCase
{
    public function setUp()
    {
        AppKernel::resetEnvironment();
        AppKernel::installDistEnvironment();
    }

    public function tearDown()
    {
        AppKernel::resetEnvironment();
    }

    abstract protected function getAdapterId();

    protected static function getKernelClass()
    {
        return 'Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel';
    }

    /**
     * @ParamProviders({"provideNbDocuments"})
     * @Groups({"index"})
     */
    public function benchIndex($params)
    {
        $manager = $this->getSearchManager($this->getAdapterId());
        $manager->purge('product');

        $nbDocuments = $params['nb_documents'];

        for ($i = 0; $i < $nbDocuments; ++$i) {
            $product = new Product();
            $product->setId($i);

            if ($i == floor($nbDocuments / 2)) {
                $product->setTitle('Needle');
            }
            {
                $product->setTitle('Hello this is a product ' . $i);
            }
            $product->setBody('To be or not to be, that is the question');
            $product->setUrl('/foobar');
            $product->setLocale('fr');

            $manager->index($product);
        }
    }

    /**
     * @ParamProviders({"provideNbDocuments"})
     * @BeforeMethods({"benchIndex"}, extend=true)
     * @Groups({"search"})
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
        return [
            [
                'nb_documents' => 50,
            ],
            [
                'nb_documents' => 100,
            ],
            [
                'nb_documents' => 200,
            ],
        ];
    }
}
