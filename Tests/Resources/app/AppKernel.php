<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Resources\app;

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;

class AppKernel extends TestKernel
{
    public static function getTempConfig()
    {
        return __DIR__ . '/config/_behat_massivesearch.yml';
    }

    public static function getEntityDir()
    {
        return __DIR__ . '/../TestBundle/Entity';
    }

    public static function getMappingDir()
    {
        return __DIR__ . '/../TestBundle/Resources/config/massive-search';
    }

    public static function resetEnvironment()
    {
        $fs = new Filesystem();

        if (file_exists(self::getMappingDir())) {
            $fs->remove(self::getMappingDir());
        }
        $fs->mkdir(self::getMappingDir());

        if (file_exists(self::getEntityDir())) {
            $fs->remove(self::getEntityDir());
        }
        $fs->mkdir(self::getEntityDir());

        if (file_exists(self::getTempConfig())) {
            $fs->remove(self::getTempConfig());
        }
        $massiveCacheDir = __DIR__ . '/cache/massive-search';

        $fs->remove(__DIR__ . '/cache/jms_serializer');

        if (file_exists($massiveCacheDir)) {
            $fs->remove($massiveCacheDir);
            mkdir($massiveCacheDir);
        }
    }

    public static function clearData()
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/cache/data');
        $fs->mkdir(__DIR__ . '/cache/data');
    }

    public static function installDistEnvironment()
    {
        $fs = new Filesystem();
        $fs->mirror(self::getMappingDir() . '-dist', self::getMappingDir());
        $fs->mirror(self::getEntityDir() . 'Dist', self::getEntityDir());
    }

    public function configure()
    {
        $this->requireBundleSets([
            'default',
            'doctrine_orm',
        ]);

        $this->addBundles([
            new \Massive\Bundle\SearchBundle\MassiveSearchBundle(),
            new \Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\TestBundle(),
        ]);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.php');
        $loader->load(__DIR__ . '/config/massivesearchbundle.yml');
        $loader->load(__DIR__ . '/../../../Resources/config/adapter_elastic.xml');
        $loader->load(__DIR__ . '/../../../Resources/config/adapter_zendlucene.xml');
    }

    public function getCacheDir()
    {
        return __DIR__ . '/cache';
    }
}
