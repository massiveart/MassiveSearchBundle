<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Unit\Search\Converter;

use Massive\Bundle\SearchBundle\Search\Converter\ZendLuceneArrayConverter;

class ZendLuceneArrayConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $converter = new ZendLuceneArrayConverter();

        $this->assertEquals('|1|2|3|', $converter->convert([1, 2, 3]));
    }
}
