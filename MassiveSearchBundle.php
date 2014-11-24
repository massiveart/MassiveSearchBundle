<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Massive\Bundle\SearchBundle\DependencyInjection\Compiler\MetadataDriverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Massive\Bundle\SearchBundle\DependencyInjection\Compiler\ZendLucenePass;

class MassiveSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MetadataDriverPass());
        $container->addCompilerPass(new ZendLucenePass());
    }
}
