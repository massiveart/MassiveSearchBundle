<?php
/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\ReIndex;

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
    public function setCheckpoint($name, $value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Only scalar values may be passed as a checkpoint value, got "%s"',
                gettype($value)
            ));
        }

        $data = $this->getCheckpoints();

        $data[$name] = $value;
        $this->saveCheckpoints($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckpoint($name, $default = null)
    {
        $data = $this->getCheckpoints();

        if (isset($data[$name])) {
            return $data[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCheckpoint($name)
    {
        $data = $this->getCheckpoints();

        if (isset($data[$name])) {
            unset($data[$name]);
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
    public function getCheckpoints()
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
}
