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

/**
 * @Groups({"adapter_test"}, extend=true)
 */
class TestBench extends AdapterBench
{
    protected function getAdapterId()
    {
        return 'massive_search.adapter.test';
    }
}
