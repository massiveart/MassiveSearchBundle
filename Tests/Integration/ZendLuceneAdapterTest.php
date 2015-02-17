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
use Massive\Bundle\SearchBundle\Search\Localization\NoopStrategy;

class ZendLuceneAdapterTest extends AdapterTestCase
{
    protected $baseDir;
    protected $filesystem;

    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->baseDir = sys_get_temp_dir() . '/massive-test-zend-lucene';
        parent::setUp();
    }

    public function purgeIndex($indexName)
    {
        if (file_exists($this->baseDir)) {
            $this->filesystem->remove($this->baseDir);
        }
        $this->filesystem->mkdir($this->baseDir);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->baseDir);
    }

    public function doGetAdapter()
    {
        return new ZendLuceneAdapter($this->getFactory(), $this->baseDir, true);
    }

    public function provideSearch()
    {
        return array(
            array('one', 1),
            array('one ', 1),
            array('roomba 870', 0),
            array('870', 0),
            array('*', 0),
            array('***', 0),
            array('???', 0),
        );
    }
}
