<?php

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber\TestSubscriber;
use Symfony\Component\Console\Tester\CommandTester;
use Massive\Bundle\SearchBundle\Command\MassiveSearchQueryCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Massive\Bundle\SearchBundle\Command\StatusCommand;

class StatusCommandTest extends BaseTestCase
{
    public function setUp()
    {
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

