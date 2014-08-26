<?php

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\EventSubscriber\TestSubscriber;
use Symfony\Component\Console\Tester\CommandTester;
use Massive\Bundle\SearchBundle\Command\MassiveSearchQueryCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class MassiveSearchQueryTest extends BaseTestCase
{
    public function setUp()
    {
        $command = new MassiveSearchQueryCommand();
        $application = new Application($this->getContainer()->get('kernel'));
        $command->setApplication($application);
        $this->tester = new CommandTester($command);
        $this->generateIndex(10);
    }

    public function testCommand()
    {
        $this->tester->execute(array(
            'query' => 'Hello',
            '--index' => 'product',
        ));

        $display = $this->tester->getDisplay();

        $this->assertSame(<<<EOT
+------------------+----+----------------------------+------------------------------------------+---------+
| Score            | ID | Title                      | Description                              | Url     |
+------------------+----+----------------------------+------------------------------------------+---------+
| 0.39580179633561 | 9  | Hello this is a product 9  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 8  | Hello this is a product 8  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 7  | Hello this is a product 7  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 6  | Hello this is a product 6  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 5  | Hello this is a product 5  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 4  | Hello this is a product 4  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 3  | Hello this is a product 3  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 2  | Hello this is a product 2  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 1  | Hello this is a product 1  | To be or not to be, that is the question | /foobar |
| 0.39580179633561 | 10 | Hello this is a product 10 | To be or not to be, that is the question | /foobar |
+------------------+----+----------------------------+------------------------------------------+---------+

EOT
        , $display);
    }
}

