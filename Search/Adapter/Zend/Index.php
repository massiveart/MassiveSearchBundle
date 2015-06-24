<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Adapter\Zend;

use ZendSearch\Lucene\Index as BaseIndex;

/**
 * This class adds the possibility to hide destructor errors
 * which generally occur when running consecutive tests.
 */
class Index extends BaseIndex
{
    /**
     * @Var boolean
     */
    private $hideDestructException = false;

    /**
     * Workaround for problems encountered when functional testing ZendSearch.
     * The __destruct is called at the end of the test suite, and throws an
     * error causing exit-code to be non-zero  even if there were no failures.
     *
     * Set to true to catch exceptions in the __destruct method
     *
     * @param bool $hideDestructException
     */
    public function setHideException($hideDestructException)
    {
        $this->hideDestructException = $hideDestructException;
    }

    public function __destruct()
    {
        try {
            parent::__destruct();
        } catch (\Exception $e) {
            if (false === $this->hideDestructException) {
                throw $e;
            }
        }
    }
}
