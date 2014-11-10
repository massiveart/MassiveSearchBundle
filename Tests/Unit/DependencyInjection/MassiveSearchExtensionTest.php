<?php
/*
 * This file is part of the Sulu CMS.
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
    protected function getContainerExtensions()
    {
        $this->container->setParameter('kernel.bundles', array('Massive\Bundle\SearchBundle\MassiveSearchBundle'));

        return array(
            new MassiveSearchExtension()
        );
    }

    public function testLoad()
    {
        $this->load();
    }

    public function provideAdapterConfig()
    {
        return array(
            array(
                'zend_lucene', 
                array(
                    'basepath' => 'foobar',
                    'hide_index_exception' => true
                ),
                array(
                    'massive_search.adapter.zend_lucene.basepath' => 'foobar',
                    'massive_search.adapter.zend_lucene.hide_index_exception' => true,
                ),
            ),
            array(
                'zend_lucene', 
                array(
                ),
                array(
                    'massive_search.adapter.zend_lucene.basepath' => '%kernel.root_dir%/data',
                    'massive_search.adapter.zend_lucene.hide_index_exception' => false,
                ),
            ),
            array(
                'elastic', 
                array(
                    'hosts' => array(
                        'localhost:8081',
                        'http://www.example.com:9091',
                    ),
                ),
                array(
                    'massive_search.adapter.elastic.hosts' => array(
                        'localhost:8081',
                        'http://www.example.com:9091',
                    )
                )
            ),
            array(
                'elastic', 
                array(
                ),
                array(
                    'massive_search.adapter.elastic.hosts' => array('localhost:9200'),
                )
            ),
        );
    }

    /**
     * @dataProvider provideAdapterConfig
     */
    public function testAdapterConfig($adapterId, $config, $expectedParameters)
    {
        $config = array(
            'adapter_id' => $adapterId,
            'adapters' => array(
                $adapterId => $config
            )
        );

        $this->load($config);

        foreach ($expectedParameters as $expectedKey => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $this->container->getParameter($expectedKey)
            );
        }
    }
}
