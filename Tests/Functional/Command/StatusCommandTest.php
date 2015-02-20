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

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Massive\Bundle\SearchBundle\Command\StatusCommand;

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
        $this->tester->execute(array(
        ));

        $this->assertEquals(0, $this->tester->getStatusCode());
    }
}
