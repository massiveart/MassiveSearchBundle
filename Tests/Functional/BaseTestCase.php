<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Functional;

use Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    private $kernels = [];

    public function setUp()
    {
        AppKernel::resetEnvironment();
        AppKernel::installDistEnvironment();
    }

    public function tearDown()
    {
        AppKernel::resetEnvironment();
    }

    protected static function getKernelClass()
    {
        return 'Massive\Bundle\SearchBundle\Tests\Resources\app\AppKernel';
    }

    protected function generateIndex($nbResults)
    {
        $nbResults = 10;
        for ($i = 1; $i <= $nbResults; ++$i) {
            $product = new Product();
            $product->setId($i);
            $product->setTitle('Hello this is a product ' . $i);
            $product->setBody('To be or not to be, that is the question');
            $product->setUrl('/foobar');
            $product->setLocale('fr');

            $this->getSearchManager()->index($product);
        }
    }

    public function getSearchManager()
    {
        $searchManager = $this->getContainer()->get('massive_search.search_manager');

        return $searchManager;
    }

    protected function getContainer($env = 'phpcr')
    {
        return $this->getKernel($env)->getContainer();
    }

    protected function getKernel($env = 'phpcr')
    {
        if (isset($this->kernels[$env])) {
            return $this->kernels[$env];
        }

        $this->kernels[$env] = new AppKernel($env, true);
        $this->kernels[$env]->boot();

        return $this->kernels[$env];
    }

    protected function getCommand($env, $name)
    {
        $kernel = $this->getKernel($env);
        $container = $kernel->getContainer();
        $application = new Application($kernel);

        $this->addCommandsToApplication($container, $application);

        return new CommandTester($application->get($name));
    }

    protected function addCommandsToApplication(ContainerInterface $container, Application $application)
    {
        if (method_exists($application, 'setCommandLoader')) {
            $application->setCommandLoader($container->get('console.command_loader'));

            return;
        }

        foreach ($container->getParameter('console.command.ids') as $id) {
            $application->add($container->get($id));
        }
    }
}
