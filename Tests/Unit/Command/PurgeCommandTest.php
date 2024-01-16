<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Command;

use Massive\Bundle\SearchBundle\Command\PurgeCommand;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class PurgeCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function setUp()
    {
        $this->searchManager = $this->prophesize(SearchManagerInterface::class);
        $this->questionHelper = $this->prophesize(QuestionHelper::class);
    }

    /**
     * It should list available indexes if no options given.
     */
    public function testNoOptions()
    {
        $this->searchManager->getIndexNames()->willReturn([
            'one', 'two',
        ]);
        $tester = $this->execute([]);
        $this->assertStringContainsString('one', $tester->getDisplay());
        $this->assertStringContainsString('two', $tester->getDisplay());
    }

    /**
     * It should show a message indicating that no indexes exist if no indexes exist.
     */
    public function testNoIndexesExist()
    {
        $this->searchManager->getIndexNames()->willReturn([]);
        $tester = $this->execute([]);
        $this->assertStringContainsString('No indexes', $tester->getDisplay());
    }

    /**
     * It should purge selected indexes.
     */
    public function testPurgeSelectedIndexes()
    {
        $this->searchManager->getIndexNames()->willReturn([
            'foobar',
            'barfoo',
        ]);
        $this->searchManager->purge('foobar')->shouldBeCalled();
        $this->searchManager->purge('barfoo')->shouldBeCalled();
        $this->questionHelper->ask(Argument::cetera())->willReturn(true);

        $this->execute([
            '--index' => ['foobar', 'barfoo'],
        ]);
    }

    /**
     * It should not purge indexes if no confirmation is received.
     */
    public function testNoPurgeNoConfirmation()
    {
        $this->searchManager->getIndexNames()->willReturn([
            'foobar',
        ]);
        $this->searchManager->purge('foobar')->shouldNotBeCalled();
        $this->questionHelper->ask(Argument::cetera())->willReturn(false);

        $this->execute([
            '--index' => ['foobar'],
        ]);
    }

    /**
     * It should purge all indexes if --all given.
     */
    public function testPurgeAll()
    {
        $this->searchManager->getIndexNames()->willReturn([
            'foobar', 'barfoo',
        ]);
        $this->searchManager->purge('foobar')->shouldBeCalled();
        $this->searchManager->purge('barfoo')->shouldBeCalled();
        $this->questionHelper->ask(Argument::cetera())->willReturn(true);

        $this->execute([
            '--all' => true,
        ]);
    }

    /**
     * It should not ask for confirmation if --force is given.
     */
    public function testForce()
    {
        $this->searchManager->getIndexNames()->willReturn([
            'foobar', 'barfoo',
        ]);
        $this->searchManager->purge('foobar')->shouldBeCalled();
        $this->searchManager->purge('barfoo')->shouldBeCalled();
        $this->questionHelper->ask(Argument::cetera())->shouldNotBeCalled();

        $this->execute([
            '--all' => true,
            '--force' => true,
        ]);
    }

    /**
     * It should list all possible indexes if an invalid index is given.
     */
    public function testInvalidIndex()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown indexes "bambi", "ralph", known indexes: "foobar", "barfoo"');

        $this->searchManager->getIndexNames()->willReturn([
            'foobar', 'barfoo',
        ]);

        $this->execute([
            '--index' => ['bambi', 'ralph'],
        ]);
    }

    private function execute($args = [])
    {
        $application = new Application();
        $application->add(new PurgeCommand(
            $this->searchManager->reveal(),
            $this->questionHelper->reveal()
        ));

        $command = $application->find('massive:search:purge');
        $tester = new CommandTester($command);
        $tester->execute($args, [
            'interactive' => false,
        ]);

        return $tester;
    }
}
