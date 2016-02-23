<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Reindex;

/**
 * Allow the storage of "checkpoints" which indicate how far
 * a reindexing process has progressed in the case that it is
 * interupted (f.e. by an error) with the aim of allowing the
 * reindexing process to resume from where it left off.
 *
 * This is basically a key/value store.
 */
class ResumeManager implements ResumeManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function setCheckpoint($providerName, $classFqn, $value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Only scalar values may be passed as a checkpoint value, got "%s"',
                gettype($value)
            ));
        }

        $data = $this->getCheckpoints();

        if (!isset($data[$providerName])) {
            $data[$providerName] = [];
        }

        $data[$providerName][$classFqn] = $value;

        $this->saveCheckpoints($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckpoint($providerName, $classFqn)
    {
        $data = $this->getCheckpoints();

        if (isset($data[$providerName][$classFqn])) {
            return $data[$providerName][$classFqn];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCheckpoints($providerName)
    {
        $data = $this->getCheckpoints();

        if (isset($data[$providerName])) {
            unset($data[$providerName]);
            $this->saveCheckpoints($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function purgeCheckpoints()
    {
        $filename = $this->getCheckpointFile();
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUnfinishedProviders()
    {
        return array_keys($this->getCheckpoints());
    }

    /**
     * Return the path to the checkpoint file.
     *
     * @return string
     */
    public function getCheckpointFile()
    {
        return sys_get_temp_dir() . '/' . str_replace('\\', '_', __CLASS__);
    }

    private function saveCheckpoints(array $data)
    {
        file_put_contents($this->getCheckpointFile(), serialize($data));
    }

    private function getCheckpoints()
    {
        $filename = $this->getCheckpointFile();
        $data = [];

        if (file_exists($filename)) {
            $data = unserialize(file_get_contents($filename));
        }

        if (!$data || !is_array($data)) {
            $data = [];
        }

        return $data;
    }
}
