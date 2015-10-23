<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Driver;

use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Metadata\Driver\FileLocatorInterface;
use Prophecy\Argument;

class XmlDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlDriver
     */
    private $xmlDriver;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    public function setUp()
    {
        $this->factory = $this->prophesize(Factory::class);
        $this->fileLocator = $this->prophesize(FileLocatorInterface::class);
        $this->reflectionClass = $this->prophesize(\ReflectionClass::class);

        $that = $this;

        $this->factory->createClassMetadata(Argument::cetera())->will(function($arguments) use($that) {
            $classMetadata = new ClassMetadata($that->reflectionClass->getName());

            return $classMetadata;
        });

        $this->factory->createMetadataProperty(Argument::cetera())->will(function($arguments) use($that) {
            return new Property($arguments[0]);
        });

        $this->factory->createMetadataField(Argument::cetera())->will(function($arguments) use($that) {
            return new Field($arguments[0]);
        });

        $this->factory->createIndexMetadata()->will(function() use($that) {
            return new IndexMetadata();
        });

        $this->xmlDriver = new XmlDriver($this->factory->reveal(), $this->fileLocator->reveal());
    }

    public function testLoadMetadataFromFile()
    {
        $this->reflectionClass->getName()->willReturn('Sulu\Bundle\ExampleBundle\Entity\Example');

        $metadata = $this->xmlDriver->loadMetadataFromFile(
            $this->reflectionClass->reveal(),
            __DIR__ . '/../../../../Resources/DataFixtures/Mapping/Example.xml'
        );

        $indexMetadata = $metadata->getIndexMetadata('_default');

        $this->assertEquals('id', $indexMetadata->getIdField()->getProperty());
        $this->assertEquals('title', $indexMetadata->getTitleField()->getProperty());
        $this->assertEquals('description', $indexMetadata->getDescriptionField()->getProperty());
        $this->assertEquals('example', $indexMetadata->getIndexName());
        $this->assertEquals('example', $indexMetadata->getCategoryName());
        $this->assertEquals('locale', $indexMetadata->getLocaleField()->getProperty());

        $fieldMappings = $indexMetadata->getFieldMapping();
        $this->assertEquals('string', $fieldMappings['title']['type']);
        $this->assertEquals('title', $fieldMappings['title']['field']->getName());
        $this->assertEquals('string', $fieldMappings['description']['type']);
        $this->assertEquals('description', $fieldMappings['description']['field']->getName());

        $options = $indexMetadata->getOptions();
        $this->assertEquals('value1', $options['key1']);
        $this->assertEquals('value2', $options['key2']);
    }

    public function testLoadMetadataFromFileWithoutOptions()
    {
        $this->reflectionClass->getName()->willReturn('Sulu\Bundle\ExampleBundle\Entity\Example');

        $metadata = $this->xmlDriver->loadMetadataFromFile(
            $this->reflectionClass->reveal(),
            __DIR__ . '/../../../../Resources/DataFixtures/Mapping/ExampleWithoutOptions.xml'
        );

        $indexMetadata = $metadata->getIndexMetadata('_default');

        $this->assertEquals('id', $indexMetadata->getIdField()->getProperty());
        $this->assertEquals('title', $indexMetadata->getTitleField()->getProperty());
        $this->assertEquals('description', $indexMetadata->getDescriptionField()->getProperty());
        $this->assertEquals('example', $indexMetadata->getIndexName());
        $this->assertEquals('example', $indexMetadata->getCategoryName());
        $this->assertEquals('locale', $indexMetadata->getLocaleField()->getProperty());

        $fieldMappings = $indexMetadata->getFieldMapping();
        $this->assertEquals('string', $fieldMappings['title']['type']);
        $this->assertEquals('title', $fieldMappings['title']['field']->getName());
        $this->assertEquals('string', $fieldMappings['description']['type']);
        $this->assertEquals('description', $fieldMappings['description']['field']->getName());

        $options = $indexMetadata->getOptions();
        $this->assertEmpty($options);
    }
}
