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

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Massive\Bundle\SearchBundle\Command\StatusCommand;
use Massive\Bundle\SearchBundle\Command\MetadataDebugCommand;

class MetadataDebugCommandTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $command = new MetadataDebugCommand();
        $application = new Application($this->getContainer()->get('kernel'));
        $command->setApplication($application);
        $this->tester = new CommandTester($command);
    }

    public function testCommand()
    {
        $this->tester->execute(array(
        ));

        $this->assertEquals(0, $this->tester->getStatusCode());
        $expected = <<<EOT
+-----------------------------------------------------------------------+--------+
| Class                                                                 | Status |
+-----------------------------------------------------------------------+--------+
| Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Car     | ok     |
| Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product | ok     |
| Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Contact | ok     |
+-----------------------------------------------------------------------+--------+

EOT;
        $this->assertEquals($expected, $this->tester->getDisplay());
    }
}
