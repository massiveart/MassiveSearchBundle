<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\MassiveSearchBundle\Tests\Unit\Search;

use Massive\Bundle\SearchBundle\Search\Localization\IndexStrategy;

class IndexStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function provideStrategy()
    {
        return array(
            array('hello', 'fr', 'hello-fr-i18n'),
            array('hello', null, 'hello'),
            array('', 'fr', '-fr-i18n'),
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

    public function provideisIndexVariantOf()
    {
        return array(
            array(
                'asdfasdf',
                'my_index',
                false,
            ),
            array(
                'my_index-fr-i18n',
                'my_index',
                true,
            ),
            array(
                'foo_bar_index-de_at-i18n',
                'foo_bar_index',
                true,
            ),
            array(
                'foo_bar_index_de_at_i18n',
                'foo_bar_index',
                false,
            ),
            array(
                'foo_bar_foo_index_de-at-i18n',
                'foo_bar_index',
                false,
            ),
        );
    }

    /**
     * @dataProvider provideisIndexVariantOf
     */
    public function testisIndexVariantOf($variantName, $indexName, $isVariant)
    {
        $strategy = new IndexStrategy();
        $result = $strategy->isIndexVariantOf($indexName, $variantName);
        $this->assertEquals($isVariant, $result);
    }
}
