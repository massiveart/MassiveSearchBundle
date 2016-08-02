<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Will be fired to index given subject.
 */
class IndexEvent extends Event
{
    /**
     * @var object
     */
    private $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * The object, which should be indexed.
     *
     * @return object
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
