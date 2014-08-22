<?php

namespace Massive\Bundle\SearchBundle\Tests\Integration;

use Symfony\Component\Filesystem\Filesystem;
use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;

class ZendLuceneAdapterTest extends AdapterTestCase
{
    protected $baseDir;
    protected $filesystem;

    public function setUp()
    {
        $this->baseDir = sys_get_temp_dir() . '/massive-test-zend-lucene';
        $this->filesystem = new Filesystem();

        if (!file_exists($this->baseDir)) {
            mkdir($this->baseDir);
        }
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->baseDir);
    }

    public function getAdapter()
    {
        return new ZendLuceneAdapter($this->baseDir);
    }
}
