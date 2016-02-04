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

class StatusCommandTest extends BaseTestCase
{
    public function testCommand()
    {
        $command = $this->getCommand('phpcr', 'massive:search:status');
        $command->execute([
        ]);

        $this->assertEquals(0, $command->getStatusCode());
    }
}
