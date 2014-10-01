<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as SymfonyCmfBaseTestCase;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber\TestSubscriber;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseTestCase extends SymfonyCmfBaseTestCase
{
    public function setUp()
    {
        $fs = new Filesystem;
        $fs->remove(__DIR__ . '/../Resources/app/data');
    }

    protected function generateIndex($nbResults)
    {
        $nbResults = 10;
        for ($i = 1; $i <= $nbResults; $i++) {
            $product = new Product();
            $product->setId($i);
            $product->setTitle('Hello this is a product '.$i);
            $product->setBody('To be or not to be, that is the question');
            $product->setUrl('/foobar');

            $this->getSearchManager()->index($product);
        }
    }

    public function getSearchManager()
    {
        $searchManager = $this->getContainer()->get('massive_search.search_manager');
        return $searchManager;
    }
}
