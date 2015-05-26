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
 * @Groups({"adapter_lucene"}, extend=true)
 */
class ZendLuceneBench extends AdapterBench
{
    protected function getAdapterId()
    {
        return 'massive_search.adapter.zend_lucene';
    }
}
