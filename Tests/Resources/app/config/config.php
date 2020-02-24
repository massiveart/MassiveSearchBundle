<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$loader->import(CMF_TEST_CONFIG_DIR . '/default.php');
$loader->import(__DIR__ . '/massivesearchbundle.yml');

if (version_compare(\Symfony\Component\HttpKernel\Kernel::VERSION, '5.0', '<')) {
    $loader->import(__DIR__ . '/disable_templating.yml');
}
