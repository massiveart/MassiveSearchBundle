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

use Symfony\Component\Filesystem\Filesystem;
use Massive\Bundle\SearchBundle\Search\Adapter\TestAdapter;

class TestAdapterTest extends AdapterTestCase
{
    public function getAdapter()
    {
        return new TestAdapter($this->getFactory());
    }
}
