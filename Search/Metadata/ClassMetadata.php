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
 * Metadata for a mapped search object. A single class
 * may have several different search mappings.
 */
class ClassMetadata extends BaseClassMetadata implements \Serializable
{
    /**
     * @var array
     */
    private $indexMetadatas = array();

    /**
     * Add an index metadata for the given context name
     *
     * @param mixed $contextName
     * @param IndexMetadata $indexMetadata
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
        $indexMetadata->setClassMetadata($this);
        $this->indexMetadatas[$contextName] = $indexMetadata;
    }

    /**
     * Return the IndexMetadata metadata instances
     *
     * @return IndexMetadata[]
     */
    public function getIndexMetadatas()
    {
        return $this->indexMetadatas;
    }

    /**
     * Return the indexmetadata for the given context
     *
     * @param string $contextName
     *
     * @return IndexMetadata
     */
    public function getIndexMetadata($contextName)
    {
        if (!isset($this->indexMetadatas[$contextName])) {
            throw new \InvalidArgumentException(sprintf(
                'Context name "%s" not known, known contexts: "%s"',
                $contextName, implode('", "', array_keys($this->indexMetadatas))
            ));
        }

        return $this->indexMetadatas[$contextName];
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        $data = parent::serialize();
        return serialize(array($data, serialize($this->indexMetadatas)));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        list($data, $indexMetadata) = unserialize($data);
        parent::unserialize($data);
        $this->indexMetadatas = unserialize($indexMetadata);
    }
}
