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
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;

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
                'field' => new Field('title'),
            ),
            'body' => array(
                'type' => 'string',
                'field' => new Field('body'),
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

        $this->assertEquals('car_admin', $metadata->getIndexName());
        $this->assertInstanceOf(
            'Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression',
            $metadata->getUrlField()
        );

        // ensure that the context if overridden
        $this->assertEquals(
            '\'/admin/#cars/edit:\' ~ object.id',
            $metadata->getUrlField()->getExpression()
        );

        $mappings = $metadata->getFieldMapping();
        $this->assertCount(3, $mappings);
        $this->assertArrayHasKey('title', $mappings);
        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression', $mappings['title']['field']);
        $this->assertEquals('object.title', $mappings['title']['field']->getExpression());
    }
}
