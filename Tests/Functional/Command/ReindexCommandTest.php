<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Functional\Command;

use Massive\Bundle\SearchBundle\Tests\Functional\BaseTestCase;

class ReindexCommandTest extends BaseTestCase
{
    /**
     * This command just fires an event.
     */
    public function testCommand()
    {
        $this->generateIndex(10);
        $tester = $this->getCommand('prod', 'massive:search:reindex');
        $tester->execute([], ['interactive' => false]);

        $this->assertEquals(0, $tester->getStatusCode());
    }

    /**
     * It should ask for configuration if a checkpoint exists.
     */
    public function testCheckpointResume()
    {
        $this->markTestSkipped(
            'We could do this as detailed here: http://symfony.com/doc/current/components/console/helpers/dialoghelper.html#testing-a-command-which-expects-input'
        );
    }

    /**
     * It show a warning if the environment is not the production environment.
     */
    public function testWarningNoEnvNotProd()
    {
        $tester = $this->getCommand('dev', 'massive:search:reindex');
        $tester->execute([], ['interactive' => false]);

        $this->assertStringContainsString('WARNING', $tester->getDisplay());
    }

    /**
     * It should not show a warning if the environment is the production environment.
     */
    public function testNoWarningNoEnvProd()
    {
        $tester = $this->getCommand('prod', 'massive:search:reindex');
        $tester->execute([], ['interactive' => false]);

        $this->assertStringNotContainsString('WARNING', $tester->getDisplay());
    }

    /**
     * The deprecated command name should still work.
     */
    public function testDeprecatedCommand()
    {
        $tester = $this->getCommand('prod', 'massive:search:index:rebuild');
        $tester->execute([], ['interactive' => false]);
        $this->assertStringContainsString('DEPRECATED: The `massive:search:index:rebuild` command is deprecated', $tester->getDisplay());
    }
}
