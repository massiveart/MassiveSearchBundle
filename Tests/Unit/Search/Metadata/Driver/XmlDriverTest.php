<?php

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Metadata\Driver;

use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\Driver\XmlDriver;

class XmlDriverTest extends \PHPUnit_Framework_TestCase
{
    const TMP_FILE = 'mapping_test';

    public function setUp()
    {
        $factory = new Factory();
        $this->locator = $this->prophesize('Metadata\Driver\FileLocatorInterface');
        $this->driver = new XmlDriver(
            $factory,
            $this->locator->reveal()
        );
    }

    public function tearDown()
    {
        $mappingPath = $this->getTmpMappingPath();
        if (file_exists($mappingPath)) {
            unlink($mappingPath);
        }
    }

    /**
     * It should load metadata from a file.
     */
    public function testLoadMetadataFromFile()
    {
        $path = $this->createMapping(<<<EOT
<massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

    <mapping class="stdClass">
        <index name="index_bicycle"/>
        <id property="id"/>
        <title property="title" />
        <category name="transport" />
        <fields>
            <field name="title" expr="object.title" type="string" />
        </fields>
    </mapping>

</massive-search-mapping>
EOT
    );
        $reflection = new \ReflectionClass('stdClass');
        $metadata = $this->driver->loadMetadataFromFile($reflection, $path);

        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata', $metadata);
        $indexMetadatas = $metadata->getIndexMetadatas();
        $this->assertCount(1, $indexMetadatas);
        $indexMetadata = reset($indexMetadatas);
        $this->assertEquals('index_bicycle', $indexMetadata->getIndexName());
        $this->assertInstanceOf('Massive\Bundle\SearchBundle\Search\Metadata\Field\Property', $indexMetadata->getIdField());
        $this->assertEquals('id', $indexMetadata->getIdField()->getProperty());
        $this->assertEquals('title', $indexMetadata->getTitleField()->getProperty());
        $this->assertEquals('transport', $indexMetadata->getCategoryName());
        $this->assertCount(1, $indexMetadata->getFieldMapping());
    }

    /**
     * It should allow the specification of reindex directives.
     */
    public function testReindexDirectives()
    {
        $path = $this->createMapping(<<<EOT
<massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

    <mapping class="stdClass">
        <index name="foo"/>
        <id property="id"/>
        <title property="foo" />
        <reindex repository-method="findLatest" />
        <fields/>
    </mapping>

</massive-search-mapping>
EOT
    );
        $reflection = new \ReflectionClass('stdClass');
        $metadata = $this->driver->loadMetadataFromFile($reflection, $path);
        $this->assertEquals('findLatest', $metadata->getReindexRepositoryMethod());
    }

    private function createMapping($mapping)
    {
        $path = $this->getTmpMappingPath();
        file_put_contents($path, $mapping);

        return $path;
    }

    private function getTmpMappingPath()
    {
        $tempDir = sys_get_temp_dir();

        return $tempDir . '/' . self::TMP_FILE;
    }
}
