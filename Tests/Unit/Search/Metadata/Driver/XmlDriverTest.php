<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata\Driver;

use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
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
        $this->reflectionClass->getName()->willReturn('Sulu\Bundle\ExampleBundle\Entity\Example');
        $this->reflectionClass->__toString()->willReturn('Example');

        $that = $this;

        $this->factory->createClassMetadata(Argument::cetera())->will(function ($arguments) use ($that) {
            $classMetadata = new ClassMetadata($that->reflectionClass->reveal()->getName());

            return $classMetadata;
        });

        $this->factory->createMetadataProperty(Argument::cetera())->will(function ($arguments) use ($that) {
            return new Property($arguments[0]);
        });

        $this->factory->createMetadataField(Argument::cetera())->will(function ($arguments) use ($that) {
            return new Field($arguments[0]);
        });

        $this->factory->createIndexMetadata()->will(function () use ($that) {
            return new IndexMetadata();
        });

        $this->factory->createMetadataExpression(Argument::cetera())->will(function ($arguments) use ($that) {
            return new Expression($arguments[0]);
        });

        $this->xmlDriver = new XmlDriver($this->factory->reveal(), $this->fileLocator->reveal());
    }

    public function testLoadMetadataFromFile()
    {
        $this->fileLocator->findFileForClass(
            $this->reflectionClass->reveal(),
            'xml'
        )->willReturn(__DIR__ . '/../../../../Resources/DataFixtures/Mapping/Example.xml');

        $metadata = $this->xmlDriver->loadMetadataForClass(
            $this->reflectionClass->reveal()
        );

        $indexMetadata = $metadata->getIndexMetadata('_default');

        $this->assertEquals('id', $indexMetadata->getIdField()->getProperty());
        $this->assertEquals('title', $indexMetadata->getTitleField()->getProperty());
        $this->assertEquals('description', $indexMetadata->getDescriptionField()->getProperty());
        $this->assertEquals('example', $indexMetadata->getIndexName()->getName());
        $this->assertEquals('locale', $indexMetadata->getLocaleField()->getProperty());

        $fieldMappings = $indexMetadata->getFieldMapping();
        $this->assertEquals('string', $fieldMappings['title']['type']);
        $this->assertEquals('title', $fieldMappings['title']['field']->getName());
        $this->assertEquals('string', $fieldMappings['description']['type']);
        $this->assertEquals('description', $fieldMappings['description']['field']->getName());
    }

    public function testLoadMetadataFromFileWithEvaluatingIndex()
    {
        $this->fileLocator->findFileForClass(
            $this->reflectionClass->reveal(),
            'xml'
        )->willReturn(__DIR__ . '/../../../../Resources/DataFixtures/Mapping/ExampleWithEvaluatingIndex.xml');

        $metadata = $this->xmlDriver->loadMetadataForClass($this->reflectionClass->reveal());

        $indexMetadata = $metadata->getIndexMetadata('_default');

        $this->assertEquals('object.getWebspace()', $indexMetadata->getIndexName()->getExpression());
    }
}
