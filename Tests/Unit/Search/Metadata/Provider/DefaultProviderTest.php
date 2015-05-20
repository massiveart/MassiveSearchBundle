<?php

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Metadata\Provider;

use Metadata\MetadataFactory;
use Massive\Bundle\SearchBundle\Search\Metadata\Provider\DefaultProvider;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Document;
use Metadata\ClassHierarchyMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface;

class DefaultProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var ClassMetadata
     */
    private $metadata1;

    /**
     * @var ClassMetadata
     */
    private $metadata2;

    /**
     * @var ClassHierarchyMetadata
     */
    private $hierarchyMetadata1;

    /**
     * @var ClassHierarchyMetadata
     */
    private $hierarchyMetadata2;

    /**
     * @var Document
     */
    private $document;

    public function setUp()
    {
        $this->metadataFactory = $this->prophesize('Metadata\MetadataFactory');
        $this->metadata1 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata');
        $this->metadata2 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata');
        $this->hierarchyMetadata1 = $this->prophesize('Metadata\ClassHierarchyMetadata');
        $this->hierarchyMetadata2 = $this->prophesize('Metadata\ClassHierarchyMetadata');
        $this->hierarchyMetadata1->getOutsideClassMetadata()->willReturn($this->metadata1);
        $this->hierarchyMetadata2->getOutsideClassMetadata()->willReturn($this->metadata2);
        $this->document = $this->prophesize('Massive\Bundle\SearchBundle\Search\Document');
        $this->provider = new DefaultProvider(
            $this->metadataFactory->reveal()
        );
    }

    /**
     * It should return metadata for the given object
     */
    public function testGetMetadataForObject()
    {
        $object = new \stdClass;
        $this->metadataFactory->getMetadataForClass('stdClass')->willReturn($this->hierarchyMetadata1->reveal());
        $metadata = $this->provider->getMetadataForObject($object);
        $this->assertSame($this->metadata1->reveal(), $metadata);
    }

    /**
     * It should return null if no metadata for an object was found
     */
    public function testReturnNullNoMetdataForObject()
    {
        $object = new \stdClass;
        $this->metadataFactory->getMetadataForClass('stdClass')->willReturn(null);
        $metadata = $this->provider->getMetadataForObject($object);
        $this->assertNull($metadata);
    }

    /**
     * It should return all metadatas
     */
    public function testGetAllMetadata()
    {
        $this->metadataFactory->getAllClassNames()->willReturn(array('one', 'two'));
        $this->metadataFactory->getMetadataForClass('one')->willReturn($this->hierarchyMetadata1->reveal());
        $this->metadataFactory->getMetadataForClass('two')->willReturn($this->hierarchyMetadata2->reveal());
        $metadatas = $this->provider->getAllMetadata();

        $this->assertSame(array(
            $this->metadata1->reveal(),
            $this->metadata2->reveal(),
        ), $metadatas);
    }

    /**
     * It should return the metadata for a search document
     */
    public function testGetMetadataForDocument()
    {
        $this->document->getClass()->willReturn('Class');
        $this->metadataFactory->getMetadataForClass('Class')->willReturn($this->hierarchyMetadata1->reveal());
        $metadata = $this->provider->getMetadataForDocument($this->document->reveal());
        $this->assertSame($this->metadata1->reveal(), $metadata);
    }

    /**
     * It should return null if no metadata for document was found
     */
    public function testReturnNullNoMetdataForDocumnet()
    {
        $this->document->getClass()->willReturn('Class');
        $this->metadataFactory->getMetadataForClass('Class')->willReturn(null);
        $metadata = $this->provider->getMetadataForDocument($this->document->reveal());
        $this->assertNull($metadata);
    }
}
