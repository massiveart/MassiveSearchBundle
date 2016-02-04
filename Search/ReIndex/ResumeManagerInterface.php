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
 * Resume managers persist and retrieve checkpoints which record
 * the progress of reindexing tasks.
 */
interface ResumeManagerInterface
{
    /**
     * Store a checkpoint. This would typically be an offset
     * from where the reindexer can subsequently resume its
     * task.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function setCheckpoint($name, $value);

    /**
     * Return the previously stored checkpoint or the default
     * value.
     *
     * @param string $name
     * @param mixed $value
     */
    public function getCheckpoint($name, $default = null);

    /**
     * Remove the named checkpoint.
     *
     * @param string $name
     */
    public function removeCheckpoint($name);

    /**
     * Purge all checkpoint.
     */
    public function purgeCheckpoints();

    /**
     * Return all checkpoints.
     *
     * @return array
     */
    public function getCheckpoints();
}
