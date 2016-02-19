<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Command;

use Massive\Bundle\SearchBundle\Search\ReIndex\ResumeManager;
use Symfony\Component\Filesystem\Filesystem;

class ResumeManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResumeManager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = new ResumeManager();
        $this->cleanUp();
    }

    public function tearDown()
    {
        $this->cleanUp();
    }

    /**
     * It should store and retrieve checkpoints for a given provider.
     */
    public function testStoreRetrieve()
    {
        $this->manager->setCheckpoint('sulu_structure', 'Foobar', 50);
        $this->assertEquals(50, $this->manager->getCheckpoint('sulu_structure', 'Foobar'));
    }

    /**
     * It should update existing keys.
     */
    public function testUpdate()
    {
        $this->manager->setCheckpoint('sulu_structure', 'BarFoo', 50);
        $this->manager->setCheckpoint('sulu_structure', 'BarFoo', 100);
        $this->assertEquals(100, $this->manager->getCheckpoint('sulu_structure', 'BarFoo'));
    }

    /**
     * It should store multiple keys.
     */
    public function testStoreMany()
    {
        $this->manager->setCheckpoint('Sulu Structure', 'Foobar', 50);
        $this->manager->setCheckpoint('Doctrine ORM Entity', 'BarFoo', 100);
        $this->assertEquals(50, $this->manager->getCheckpoint('Sulu Structure', 'Foobar'));
        $this->assertEquals(100, $this->manager->getCheckpoint('Doctrine ORM Entity', 'BarFoo'));
    }

    /**
     * It should purge the checkpoints.
     */
    public function testPurge()
    {
        $this->manager->setCheckpoint('Sulu Structure', 'Foobar', 50);
        $this->manager->setCheckpoint('Doctrine ORM Entity', 'Foobar', 100);
        $this->manager->purgeCheckpoints();

        $this->assertEquals([], $this->manager->getUnfinishedProviders());
    }

    /**
     * It should remove checkpoints for a given provider.
     */
    public function testRemove()
    {
        $this->manager->setCheckpoint('Sulu Structure', 'Foobar', 50);
        $this->manager->setCheckpoint('Doctrine ORM Entity', 'BarFoo', 100);
        $this->manager->removeCheckpoints('Sulu Structure');

        $this->assertEquals(['Doctrine ORM Entity'], $this->manager->getUnfinishedProviders());
    }

    /**
     * It should return a specific checkpoint value.
     */
    public function testGetCheckpoint()
    {
        $this->manager->setCheckpoint('Sulu Structure', 'Foobar', 50);
        $this->manager->setCheckpoint('Doctrine ORM Entity', 'BarFoo', 100);

        $this->assertEquals(50, $this->manager->getCheckpoint('Sulu Structure', 'Foobar'));
    }

    /**
     * It should throw an exception if a non-scalar value is passed to setCheckpoint.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Only scalar
     */
    public function testOnlyScalar()
    {
        $this->manager->setCheckpoint('ha', 'Fa', new \stdClass());
    }

    private function cleanUp()
    {
        $filesystem = new Filesystem();
        $checkpointFile = $this->manager->getCheckpointFile();
        $filesystem->remove($checkpointFile);
    }
}
