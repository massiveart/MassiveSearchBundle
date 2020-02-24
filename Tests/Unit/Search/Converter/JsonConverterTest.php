<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Unit\Search\Converter;

use Massive\Bundle\SearchBundle\Search\Converter\Types\JsonConverter;
use PHPUnit\Framework\TestCase;

class JsonConverterTest extends TestCase
{
    public function testConvert()
    {
        $converter = new JsonConverter();
        $this->assertEquals(json_encode(['test-1' => 'test-2'], true), $converter->convert(['test-1' => 'test-2']));
    }
}
