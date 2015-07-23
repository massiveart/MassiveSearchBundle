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

use Massive\Bundle\SearchBundle\Command\StatusCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class StatusCommandTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $command = new StatusCommand();
        $application = new Application($this->getContainer()->get('kernel'));
        $command->setApplication($application);
        $this->tester = new CommandTester($command);
    }

    public function testCommand()
    {
        $this->tester->execute([
        ]);

        $this->assertEquals(0, $this->tester->getStatusCode());
    }
}
