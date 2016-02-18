<?php

namespace Massive\Bundle\SearchBundle\Tests\Unit\Command;

use Massive\Bundle\SearchBundle\Command\ReindexCommand;
use Massive\Bundle\SearchBundle\Search\ReIndex\ResumeManagerInterface;
use Massive\Bundle\SearchBundle\Search\SearchManager;
use Massive\Bundle\SearchBundle\Search\ReIndex\ReIndexProviderRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Helper\QuestionHelper;
use Prophecy\Argument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Massive\Bundle\SearchBundle\Search\ReIndex\ReIndexProviderInterface;

class ReindexCommandTest extends \PHPUnit_Framework_TestCase
{
    private $resumeManager;
    private $searchManager;
    private $providerRegistry;
    private $questionHelper;

    private $provider1;

    public function setUp()
    {
        $this->resumeManager = $this->prophesize(ResumeManagerInterface::class);
        $this->searchManager = $this->prophesize(SearchManager::class);
        $this->providerRegistry = $this->prophesize(ReIndexProviderRegistry::class);
        $this->questionHelper = $this->prophesize(QuestionHelper::class);

        $this->provider1 = $this->prophesize(ReIndexProviderInterface::class);
    }

    /**
     * It should show a warning if the environment is not prod.
     */
    public function testEnvProdWarning()
    {
        $this->providerRegistry->getProviders()->willReturn(array());
        $tester = $this->execute('dev', array());
        $this->assertContains('WARNING: You are running', $tester->getDisplay());
    }

    /**
     * If there are checkpoints it should ask the user if they want to resume.
     */
    public function testCheckpointAsk()
    {
        $this->providerRegistry->getProviders()->willReturn(array());
        $this->resumeManager->getUnfinishedProviders()->willReturn(array(
            'foo'
        ));
        $this->questionHelper->ask(
            Argument::type(InputInterface::class),
            Argument::type(OutputInterface::class),
            Argument::type(ConfirmationQuestion::class),
            true
        )->shouldBeCalled();
        $this->execute('prod', array());
    }

    /**
     * It should index objects from providers in batches.
     */
    public function testIndexInBatches()
    {
        $classFqn = 'stdClass';
        $count = 100;
        $providerName = 'provider';

        $objects = array();
        for ($i = 0; $i <= 100; $i++) {
            $objects[] = new \stdClass();
        }
        $batch1 = array_slice($objects, 0, 50);
        $batch2 = array_slice($objects, 50);

        $this->resumeManager->getUnfinishedProviders()->willReturn(array());
        $this->providerRegistry->getProviders()->willReturn(array(
            $providerName => $this->provider1->reveal()
        ));
        $this->provider1->getClassFqns()->willReturn(array($classFqn));
        $this->provider1->getCount('stdClass')->willReturn($count);
        $this->provider1->provide('stdClass', 0, 50)->willReturn($batch1);
        $this->provider1->provide('stdClass', 50, 50)->willReturn($batch2);
        $this->provider1->provide('stdClass', 100, 50)->willReturn(array());

        $this->resumeManager->getCheckpoint($providerName, $classFqn)->willReturn(null);
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 0)->shouldBeCalled();
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 50)->shouldBeCalled();
        $this->resumeManager->removeCheckpoints($providerName)->shouldBeCalled();

        $tester = $this->execute('prod', array());

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

        $this->resumeManager->getUnfinishedProviders()->willReturn(array());
        $this->providerRegistry->getProviders()->willReturn(array(
            $providerName => $this->provider1->reveal()
        ));
        $this->provider1->getClassFqns()->willReturn(array($classFqn));
        $this->provider1->getCount('stdClass')->willReturn($count);
        $this->provider1->provide('stdClass', 23, 50)->willReturn(array(new \stdClass));
        $this->provider1->provide('stdClass', 73, 50)->willReturn(array());

        $this->resumeManager->getCheckpoint($providerName, $classFqn)->willReturn(23);
        $this->resumeManager->setCheckpoint($providerName, $classFqn, 23)->shouldBeCalled();
        $this->resumeManager->removeCheckpoints($providerName)->shouldBeCalled();

        $this->execute('prod', array());
    }

    private function execute($env, $args = array())
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
        $tester->execute($args, array(
            'interactive' => false,
        ));

        return $tester;
    }
}
