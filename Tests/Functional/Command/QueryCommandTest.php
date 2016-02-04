<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;

class QueryCommandTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->generateIndex(10);
    }

    public function testCommand()
    {
        $command = $this->getCommand('phpcr', 'massive:search:query');
        $command->execute([
            'query' => 'Hello',
            '--index' => ['product'],
        ]);

        $display = $command->getDisplay();
        $display = explode("\n", $display);
        $this->assertCount(16, $display);
    }
}
