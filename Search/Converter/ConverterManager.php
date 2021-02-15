<?php

/*
 * This file is part of the MassiveSearchBundle
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Converter;

use Massive\Bundle\SearchBundle\Search\Document;

/**
 * Implements basic converter manager.
 */
class ConverterManager implements ConverterManagerInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converter = [];

    /**
     * Add a converter to the manager.
     *
     * @param string $from source format
     */
    public function addConverter($from, ConverterInterface $converter)
    {
        $this->converter[$from] = $converter;
    }

    public function convert($value, $from/*, Document $document = null*/)
    {
        $document = null;
        if (\count(\func_get_args()) > 2) {
            $document = \func_get_arg(2);
        }

        if (null !== $document && !($document instanceof Document)) {
            throw new \InvalidArgumentException(
                \sprintf('Argument "$document" must be a "%s", "%s" given!', Document::class, \gettype($document))
            );
        }

        if (!$this->hasConverter($from)) {
            throw new ConverterNotFoundException($from);
        }

        return $this->converter[$from]->convert($value, $document);
    }

    public function hasConverter($from)
    {
        return \array_key_exists($from, $this->converter);
    }
}
