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

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Symfony\Component\Console\Tester\CommandTester;
use Massive\Bundle\SearchBundle\Command\QueryCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class QueryCommandTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $command = new QueryCommand();
        $application = new Application($this->getContainer()->get('kernel'));
        $command->setApplication($application);
        $this->tester = new CommandTester($command);
        $this->generateIndex(10);
    }

    public function testCommand()
    {
        $this->tester->execute(array(
            'query' => 'Hello',
            '--index' => array('product'),
        ));

        $display = $this->tester->getDisplay();
        $display = explode("\n", $display);
        $this->assertCount(16, $display);
    }
}
