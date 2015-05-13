<?php

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Metadata\Provider;

use Massive\Bundle\SearchBundle\Search\Metadata\Provider\ChainProvider;

class ChainProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->provider1 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface');
        $this->provider2 = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface');
        $this->metadata = $this->prophesize('Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata');
        $this->document = $this->prophesize('Massive\Bundle\SearchBundle\Search\Document');

        $this->chainProvider = new ChainProvider(array(
            $this->provider1->reveal(),
            $this->provider2->reveal()
        ));
    }

    /**
     * It should get all metadatas
     */
    public function testGetAllMetadatas()
    {
        $this->provider1->getAllMetadata()->willReturn(array($this->metadata->reveal()));
        $this->provider2->getAllMetadata()->willReturn(array($this->metadata->reveal()));
        $metadatas = $this->chainProvider->getAllMetadata();

        $this->assertEquals(array(
            $this->metadata->reveal(),
            $this->metadata->reveal(),
        ), $metadatas);
    }

    /**
     * It should return metadata for the given object
     */
    public function testGetMetadataForObject()
    {
        $object = new \stdClass;
        $this->provider1->getMetadataForObject($object)->willReturn($this->metadata->reveal());
        $metadata = $this->chainProvider->getMetadataForObject($object);
        $this->assertSame($this->metadata->reveal(), $metadata);
    }

    /**
     * It should return the metadata for a search document
     */
    public function testGetMetadataForDocument()
    {
        $this->provider1->getMetadataForDocument($this->document->reveal())->willReturn(null);
        $this->provider2->getMetadataForDocument($this->document->reveal())->willReturn($this->metadata->reveal());
        $metadata = $this->chainProvider->getMetadataForDocument($this->document->reveal());
        $this->assertSame($this->metadata->reveal(), $metadata);
    }
}
