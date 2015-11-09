<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle;

use Massive\Bundle\SearchBundle\DependencyInjection\Compiler\ConverterPass;
use Massive\Bundle\SearchBundle\DependencyInjection\Compiler\MetadataDriverPass;
use Massive\Bundle\SearchBundle\DependencyInjection\Compiler\MetadataProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MassiveSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MetadataDriverPass());
        $container->addCompilerPass(new MetadataProviderPass());
        $container->addCompilerPass(new ConverterPass());
    }
}
