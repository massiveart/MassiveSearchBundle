<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

/**
 * Represents the 
 */
class ClassMetadata extends BaseClassMetadata
{
    /**
     * @var array
     */
    private $indexMetadatas = array();

    /**
     * Add some field mapping
     */
    public function addIndexMetadata($contextName, IndexMetadata $indexMetadata)
    {
        if (isset($this->indexMetadatas[$contextName])) {
            throw new \InvalidArgumentException(sprintf(
                'Context name "%s" has already been registered',
                $contextName
            ));
        }

        $indexMetadata->setName($this->name);
        $this->indexMetadatas[$contextName] = $indexMetadata;
    }

    public function getIndexMetadatas()
    {
        return $this->indexMetadatas;
    }

    public function getName()
    {
        return $thus->name;
    }
}

