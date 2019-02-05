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

use Massive\Bundle\SearchBundle\Command\ReindexCommand;
use Massive\Bundle\SearchBundle\Search\Reindex\LocalizedReindexProviderInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\ReindexProviderRegistry;
use Massive\Bundle\SearchBundle\Search\Reindex\ResumeManagerInterface;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Tester\CommandTester;

class ReindexCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResumeManagerInterface
     */
    private $resumeManager;

    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var ReindexProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @var ReindexProviderInterface
     */
    private $provider1;

    /**
     * @var LocalizedReindexProviderInterface
     */
    private $localizedProvider1;

    public function setUp()
    {
        $this->resumeManager = $this->prophesize(ResumeManagerInterface::class);
        $this->searchManager = $this->prophesize(SearchManager::class);
        $this->providerRegistry = $this->prophesize(ReindexProviderRegistry::class);
        $this->questionHelper = $this->prophesize(QuestionHelper::class);

        $this->provider1 = $this->prophesize(ReindexProviderInterface::class);
        $this->localizedProvider1 = $this->prophesize(LocalizedReindexProviderInterface::class);
    }

    /**
     * It should show a warning if the environment is not prod.
     */
    public function testEnvProdWarning()
    {
        $this->resumeManager->getUnfinishedProviders()->willReturn([]);
        $this->providerRegistry->getProviders()->willReturn([]);
        $tester = $this->execute('dev', []);

        $this->assertContains('WARNING: You are running', $tester->getDisplay());
    }

    /**
     * If there are checkpoints it should ask the user if they want to resume.
     */
    public function testCheckpointAsk()
    {
        $this->providerRegistry->getProviders()->willReturn([]);
        $this->resumeManager->getUnfinishedProviders()->willReturn([
            'foo',
        ]);
        $this->questionHelper->ask(
            Argument::type(InputInterface::class),
            Argument::type(OutputInterface::class),
            Argument::type(ConfirmationQuestion::class),
            true
        )->shouldBeCalled();
        $this->execute('prod', []);
    }

    /**
     * It should index objects from providers in batches.
     */
    public function testIndexInBatches()
    {
        $classFqn = 'stdClass';
        $count = 100;
        $providerName = 'provider';

        $objects = [];
        for ($i = 0; $i <= 100; ++$i) {
            $objects[] = new \stdClass();
        }
        $batch1 = array_slice($objects, 0, 50);
        $batch2 = array_slice($objects, 50);

        $this->resumeManager->getUnfinishedProviders()->willReturn([]);
        $this->providerRegistry->getProviders()->willReturn([
            $providerName => $this->provider1->reveal(),
        ]);
        $this->provider1->getClassFqns()->willReturn([$classFqn]);
        $this->provider1->getCount('stdClass')->willReturn($count);
        $this->provider1->provide('stdClass', 0, 50)->willReturn($batch1);
        $this->provider1->provide('stdClass', 50, 50)->willReturn($batch2);
        $this->provider1->provide('stdClass', 100, 50)->willReturn([]);
        $this->provider1->cleanUp('stdClass')->shouldBeCalledTimes(3);

        $this->resumeManager->getCheckpoint($providerName, $classFqn)->willReturn(null);
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 50)->shouldBeCalled();
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 100)->shouldBeCalled();
        $this->resumeManager->removeCheckpoints($providerName)->shouldBeCalled();

        $tester = $this->execute('prod', []);

        $this->assertContains(
            'reindexing "100" instances of "stdClass"',
            $tester->getDisplay()
        );
    }

    /**
     * It should index all translations of objects when a localized provider is used.
     */
    public function testIndexLocalized()
    {
        $classFqn = 'stdClass';
        $count = 100;
        $providerName = 'provider';
        $batch = array_fill(0, 2, new \stdClass());

        $this->resumeManager->getUnfinishedProviders()->willReturn([]);
        $this->providerRegistry->getProviders()->willReturn([
            $providerName => $this->localizedProvider1->reveal(),
        ]);
        $this->localizedProvider1->getClassFqns()->willReturn([$classFqn]);
        $this->localizedProvider1->getCount('stdClass')->willReturn($count);
        $this->localizedProvider1->provide('stdClass', 0, 50)->willReturn($batch);
        $this->localizedProvider1->provide('stdClass', 50, 50)->willReturn([]);
        $this->localizedProvider1->cleanUp('stdClass')->shouldBeCalledTimes(2);

        foreach ($batch as $object) {
            $this->localizedProvider1->getLocalesForObject($object)->willReturn(['de', 'fr']);
            $this->localizedProvider1->translateObject($object, 'de')
                ->shouldBeCalled()
                ->willReturn($object);
            $this->localizedProvider1->translateObject($object, 'fr')
                ->shouldBeCalled()
                ->willReturn($object);
        }

        $this->resumeManager->getCheckpoint($providerName, $classFqn)->willReturn(null);
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 50)->shouldBeCalled();
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 100)->shouldBeCalled();
        $this->resumeManager->removeCheckpoints($providerName)->shouldBeCalled();

        $tester = $this->execute('prod', []);

        $this->assertContains(
            'reindexing "100" instances of "stdClass"',
            $tester->getDisplay()
        );
    }

    /**
     * It should resume indexing.
     */
    public function testIndexResume()
    {
        $classFqn = 'stdClass';
        $count = 100;
        $providerName = 'provider';

        $this->resumeManager->getUnfinishedProviders()->willReturn([]);
        $this->providerRegistry->getProviders()->willReturn([
            $providerName => $this->provider1->reveal(),
        ]);
        $this->provider1->getClassFqns()->willReturn([$classFqn]);
        $this->provider1->getCount('stdClass')->willReturn($count);
        $this->provider1->provide('stdClass', 23, 50)->willReturn([new \stdClass()]);
        $this->provider1->provide('stdClass', 73, 50)->willReturn([]);
        $this->provider1->cleanUp('stdClass')->shouldBeCalledTimes(2);

        $this->resumeManager->getCheckpoint($providerName, $classFqn)->willReturn(23);
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 73)->shouldBeCalled();
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 100)->shouldBeCalled();
        $this->resumeManager->removeCheckpoints($providerName)->shouldBeCalled();

        $this->execute('prod', []);
    }

    private function execute($env, $args = [])
    {
        $application = new Application();
        $application->add(new ReindexCommand(
            $this->resumeManager->reveal(),
            $this->searchManager->reveal(),
            $this->providerRegistry->reveal(),
            $env,
            $this->questionHelper->reveal()
        ));

        $command = $application->find('massive:search:reindex');
        $tester = new CommandTester($command);
        $tester->execute($args, [
            'interactive' => false,
        ]);

        return $tester;
    }
}
