<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Integration;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class MetadataTest extends BaseTestCase
{
    public function setUp()
    {
        $this->metadataFactory = $this->getContainer()->get('massive_search.metadata.factory');
    }

    public function testProductMetadata()
    {
        $metadata = $this->metadataFactory->getMetadataForClass('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product');

        $this->assertNotNull($metadata);
        $metadata = $metadata->getOutsideClassMetadata();

        $this->assertEquals(array(
            'title' => array(
                'type' => 'string',
            ),
            'body' => array(
                'type' => 'string',
            ),
        ), $metadata->getFieldMapping());

        $this->assertEquals('product', $metadata->getIndexName());
        $this->assertEquals('id', $metadata->getIdField()->getProperty());
    }

    public function testCarMetadata()
    {
        $metadata = $this->metadataFactory->getMetadataForClass('Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Car');
        $this->assertNotNull($metadata);
        $metadata = $metadata->getOutsideClassMetadata();

        $this->assertEquals('car', $metadata->getIndexName());
        $this->assertInstanceOf(
            'Massive\Bundle\SearchBundle\Search\Metadata\Expression',
            $metadata->getUrlField()
        );

        // ensure that the context if overridden
        $this->assertEquals(
            '\'/admin/#cars/edit:\' ~ object.id',
            $metadata->getUrlField()->getExpression()
        );
    }
}
