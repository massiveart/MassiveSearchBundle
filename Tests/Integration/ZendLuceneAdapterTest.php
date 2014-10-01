<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Integration;

use Symfony\Component\Filesystem\Filesystem;
use Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter;

class ZendLuceneAdapterTest extends AdapterTestCase
{
    protected $baseDir;
    protected $filesystem;

    public function setUp()
    {
        parent::setUp();
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
        return new ZendLuceneAdapter($this->getFactory(), $this->baseDir);
    }
}
