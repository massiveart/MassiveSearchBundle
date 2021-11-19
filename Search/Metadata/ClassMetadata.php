<?php

/*
 * This file is part of the MassiveSearchBundle
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
    private $indexMetadatas = [];

    /**
     * @var string
     */
    private $repositoryMethod;

    /**
     * Add an index metadata for the given context name.
     *
     * @param mixed $contextName
     */
    public function addIndexMetadata($contextName, IndexMetadata $indexMetadata)
    {
        if (isset($this->indexMetadatas[$contextName])) {
            throw new \InvalidArgumentException(\sprintf(
                'Context name "%s" has already been registered',
                $contextName
            ));
        }

        $indexMetadata->setName($this->name);
        $indexMetadata->setClassMetadata($this);
        $this->indexMetadatas[$contextName] = $indexMetadata;
    }

    /**
     * Return the IndexMetadata metadata instances.
     *
     * @return IndexMetadata[]
     */
    public function getIndexMetadatas()
    {
        return $this->indexMetadatas;
    }

    /**
     * Return the indexmetadata for the given context.
     *
     * @param string $contextName
     *
     * @return IndexMetadata
     */
    public function getIndexMetadata($contextName)
    {
        if (!isset($this->indexMetadatas[$contextName])) {
            throw new \InvalidArgumentException(\sprintf(
                'Context name "%s" not known, known contexts: "%s"',
                $contextName,
                \implode('", "', \array_keys($this->indexMetadatas))
            ));
        }

        return $this->indexMetadatas[$contextName];
    }

    public function serialize()
    {
        $data = parent::serialize();

        return \serialize([$data, \serialize($this->indexMetadatas), $this->repositoryMethod]);
    }

    public function unserialize($data)
    {
        list($data, $indexMetadata, $this->repositoryMethod) = \unserialize($data);
        parent::unserialize($data);
        $this->indexMetadatas = \unserialize($indexMetadata);
    }

    public function __serialize(): array
    {
        $data = parent::__serialize();

        return [$data, $this->indexMetadatas, $this->repositoryMethod];
    }

    public function __unserialize(array $data): void
    {
        list($parentData, $indexMetadata, $this->repositoryMethod) = $data;
        parent::__unserialize($parentData);
        $this->indexMetadatas = $indexMetadata;
    }

    /**
     * If specified, the reindex repsoitory method will be used to indicate a method
     * which can be used to modify the query builder (e.g. to exclude certain objects
     * from the index).
     *
     * @deprecated Returning anything from this method is deprecated. It will be passed a query builder
     */
    public function getReindexRepositoryMethod()
    {
        return $this->repositoryMethod;
    }

    /**
     * Set the repository method which should be used when reindexing.
     *
     * @param string $repositoryMethod
     */
    public function setReindexRepositoryMethod($repositoryMethod)
    {
        $this->repositoryMethod = $repositoryMethod;
    }
}
