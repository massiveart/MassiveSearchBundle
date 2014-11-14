<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Integration;

use Massive\Bundle\SearchBundle\Search\Adapter\TestAdapter;

class TestAdapterTest extends AdapterTestCase
{
    public function doGetAdapter()
    {
        return new TestAdapter($this->getFactory());
    }

    public function purgeIndex($indexName)
    {
        // nothing...
    }

    public function provideSearch()
    {
        return array(
            array('one', 1),
            array('one ', 1),
            array('roomba 870', 0),
            array('870', 0),
            array('*', 0),
            array('***', 0),
            array('???', 0),
        );
    }
}
