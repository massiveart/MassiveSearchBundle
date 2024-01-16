<?php

require_once __DIR__ . '/../vendor/symfony-cmf/testing/bootstrap/bootstrap.php';

if (!\trait_exists('Prophecy\PhpUnit\ProphecyTrait')) {
    require_once __DIR__ . '/ProphecyTrait.php';
}
