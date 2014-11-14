<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\MassiveSearchBundle\Tests\Unit\Search;

use Prophecy\PhpUnit\ProphecyTestCase;
use Massive\Bundle\SearchBundle\Search\Localization\IndexStrategy;

class IndexStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function provideStrategy()
    {
        return array(
            array('hello', 'fr', 'hello_fr'),
            array('hello', null, 'hello'),
            array('', 'fr', '_fr'),
        );
    }

    /**
     * @dataProvider provideStrategy
     */
    public function testStrategy($indexName, $locale, $expected)
    {
        $strategy = new IndexStrategy();
        $res = $strategy->localizeIndexName($indexName, $locale);
        $this->assertEquals($expected, $res);
    }
}
