<?php

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

        $fs->remove(__DIR__ . '/../Resources/app/data');
    }

    public static function installDistEnvironment()
    {
        $fs = new Filesystem();
        $fs->mirror(self::getMappingDir() . '-dist', self::getMappingDir());
        $fs->mirror(self::getEntityDir() . 'Dist', self::getEntityDir());
    }

    public function configure()
    {
        $this->requireBundleSets(array(
            'default',
            'doctrine_orm',
        ));

        $this->addBundles(array(
            new \Massive\Bundle\SearchBundle\MassiveSearchBundle(),
            new \Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\TestBundle()
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.php');

        if ($this->getEnvironment() === 'integration') {
            if (file_exists(self::getTempConfig())) {
                $loader->load(self::getTempConfig());
            }
        } else {
            $loader->load(__DIR__ . '/config/massivesearchbundle.yml');
        }
    }
}
