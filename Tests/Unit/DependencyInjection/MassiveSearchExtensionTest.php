<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Unit\DependencyInjection;

use Massive\Bundle\SearchBundle\DependencyInjection\MassiveSearchExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class MassiveSearchExtensionTest extends AbstractExtensionTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.root_dir', '/some/path');
        $this->container->setParameter('kernel.cache_dir', __DIR__ . '/../../Resources/app/cache');
        $this->container->setParameter('kernel.debug', false);
        $this->container->register('event_dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher');
    }

    protected function getContainerExtensions()
    {
        $this->container->setParameter('kernel.bundles', ['Massive\Bundle\SearchBundle\MassiveSearchBundle']);
        $this->container->setParameter('kernel.root_dir', __DIR__ . '/../../Resources/app');

        return [
            new MassiveSearchExtension(),
        ];
    }

    public function testLoad()
    {
        $this->load();
    }

    public function provideLocalizationStrategy()
    {
        return [
            [
                [
                ],
                'noop',
                [
                    'localization_strategy' => 'noop',
                ],
                'noop',
                [
                    'localization_strategy' => 'index',
                ],
                'index',
            ],
        ];
    }

    /**
     * @dataProvider provideLocalizationStrategy
     */
    public function testLocalizationStrategy($config, $expectedStrategy)
    {
        $this->load($config);

        $aliasTarget = $this->container->getAlias('massive_search.localization_strategy');
        $this->assertEquals(
            'massive_search.localization_strategy.' . $expectedStrategy,
            $aliasTarget
        );
    }

    public function provideAdapterConfig()
    {
        return [
            [
                'zend_lucene',
                [
                    'basepath' => 'foobar',
                    'hide_index_exception' => true,
                    'encoding' => 'UTF-8',
                ],
                [
                    'massive_search.adapter.zend_lucene.basepath' => 'foobar',
                    'massive_search.adapter.zend_lucene.hide_index_exception' => true,
                ],
            ],
            [
                'zend_lucene',
                [
                ],
                [
                    'massive_search.adapter.zend_lucene.basepath' => '%kernel.root_dir%/data',
                    'massive_search.adapter.zend_lucene.hide_index_exception' => false,
                ],
            ],
            [
                'elastic',
                [
                    'hosts' => [
                        'localhost:8081',
                        'http://www.example.com:9091',
                    ],
                ],
                [
                    'massive_search.adapter.elastic.hosts' => [
                        'localhost:8081',
                        'http://www.example.com:9091',
                    ],
                ],
            ],
            [
                'elastic',
                [
                ],
                [
                    'massive_search.adapter.elastic.hosts' => ['localhost:9200'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideAdapterConfig
     */
    public function testAdapterConfig($adapter, $config, $expectedParameters)
    {
        $config = [
            'adapter' => $adapter,
            'adapters' => [
                $adapter => $config,
            ],
        ];

        $this->load($config);

        foreach ($expectedParameters as $expectedKey => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $this->container->getParameter($expectedKey)
            );
        }

        $serviceId = 'massive_search.adapter.' . $adapter;
        $this->container->get($serviceId);
    }

    public function providePersistence()
    {
        return [
            [null, []],
            [
                'doctrine_orm',
                [
                    'massive_search.search.event_subscriber.doctrine_orm',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providePersistence
     */
    public function testPersistence($persistenceName = null, $expectedServices)
    {
        $config = [];

        if ($persistenceName) {
            $config['persistence'][$persistenceName]['enabled'] = true;
        }

        $this->load($config);
        $this->compile();

        foreach ($expectedServices as $serviceId) {
            $this->container->get($serviceId);
        }
    }
}
