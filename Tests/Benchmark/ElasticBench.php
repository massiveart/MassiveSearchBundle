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

/**
 * @processIsolation iteration
 */
class ElasticBench extends AdapterBench implements Benchmark
{
    protected function getAdapterId()
    {
        return 'massive_search.adapter.elastic';
    }
}
