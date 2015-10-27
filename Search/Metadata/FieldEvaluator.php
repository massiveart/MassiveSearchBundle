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

use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Field;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Property;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Evaluate the value of fields.
 */
class FieldEvaluator
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var PropertyAccess
     */
    private $accessor;

    /**
     * @param Factory $factory
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Evaluate the value from the given object and field.
     *
     * @param mixed $object
     * @param mixed $field
     */
    public function getValue($object, FieldInterface $field)
    {
        try {
            switch (get_class($field)) {
                case 'Massive\Bundle\SearchBundle\Search\Metadata\Field\Property':
                    return $this->getPropertyValue($object, $field);
                case 'Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression':
                    return $this->getExpressionValue($object, $field);
                case 'Massive\Bundle\SearchBundle\Search\Metadata\Field\Field':
                    return $this->getFieldValue($object, $field);
                case 'Massive\Bundle\SearchBundle\Search\Metadata\Field\Value':
                    return $field->getValue();
            }
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                'Error encountered when trying to determine value from object "%s"',
                get_class($object)
            ), null, $e);
        }

        throw new \RuntimeException(sprintf(
            'Unknown field type "%s" when trying to convert "%s" into a search document',
            get_class($field),
            get_class($object)
        ));
    }

    /**
     * Evaluate a property (using PropertyAccess).
     *
     * @param mixed $object
     * @param Property $field
     */
    private function getPropertyValue($object, Property $field)
    {
        return $this->accessor->getValue($object, $field->getProperty());
    }

    /**
     * Return a value determined from the name of the field
     * rather than an explicit property.
     *
     * If the object is an array, then force the array syntax.
     *
     * @param mixed $object
     * @param Field $field
     */
    private function getFieldValue($object, Field $field)
    {
        if (is_array($object)) {
            $path = '[' . $field->getName() . ']';
        } else {
            $path = $field->getName();
        }

        return $this->accessor->getValue($object, $path);
    }

    /**
     * Evaluate an expression (ExpressionLanguage).
     *
     * @param mixed $object
     * @param Expression $field
     */
    private function getExpressionValue($object, Expression $field)
    {
        try {
            return $this->expressionLanguage->evaluate($field->getExpression(), [
                'object' => $object,
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'Error encountered when evaluating expression "%s"',
                $field->getExpression()
            ), null, $e);
        }
    }
}
