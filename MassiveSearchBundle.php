<?php

namespace Massive\Bundle\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Massive\Bundle\SearchBundle\DependencyInjection\Compiler\MetadataDriverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MassiveSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MetadataDriverPass());
    }
}
