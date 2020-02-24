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

class TestContext extends AbstractSearchManagerContext
{
    public function __construct()
    {
        parent::__construct('massive_search.adapter.test');
    }
}
