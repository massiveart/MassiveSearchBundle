<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once __DIR__ . '/../vendor/symfony-cmf/testing/bootstrap/bootstrap.php';

if (!\trait_exists('Prophecy\PhpUnit\ProphecyTrait')) {
    require_once __DIR__ . '/ProphecyTrait.php';
}
