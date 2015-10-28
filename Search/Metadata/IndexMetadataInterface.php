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

/**
 * Metadata for searchable objects.
 */
interface IndexMetadataInterface
{
    /**
     * The FQCN of the mapped class this index represents.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the FCQN of the class this index represents.
     *
     * @param string
     */
    public function setName($name);

    /**
     * Return the search index name to use for the mapped class.
     *
     * @return string
     */
    public function getIndexName();

    /**
     * Set the search index name.
     *
     * @param string $indexName
     */
    public function setIndexName($indexName);

    /**
     * Return the field mapping.
     *
     * @return array
     */
    public function getFieldMapping();

    /**
     * Set the field mapping.
     *
     * @param array
     */
    public function setFieldMapping($fieldMapping);

    /**
     * Add a field mapping.
     *
     * @param string
     * @param FieldInterface
     */
    public function addFieldMapping($name, $mapping);

    /**
     * Return the name of the field on the mapped document which
     * represents the ID.
     *
     * @return FieldInterface
     */
    public function getIdField();

    /**
     * Set the name of the field on the mapped document which represents
     * the ID field.
     *
     * @param FieldInterface $idField
     */
    public function setIdField($idField);

    /**
     * Return the name of the field on the mapped document which
     * represents the URL (should be named URI).
     *
     * @return FieldInterface
     */
    public function getUrlField();

    /**
     * Set the name of the field representing the URL.
     *
     * @param FieldInterface $urlField
     */
    public function setUrlField($urlField);

    /**
     * Return the name of the field on the mapped document which
     * represents the ID.
     *
     * @return FieldInterface
     */
    public function getTitleField();

    /**
     * Set the name of the field representing the title.
     *
     * @param FieldInterface $titleField
     */
    public function setTitleField($titleField);

    /**
     * Return the name of the field on the mapped document which
     * represents the description.
     *
     * @return FieldInterface
     */
    public function getDescriptionField();

    /**
     * Set the name of the field representing the description.
     *
     * @param FieldInterface $descriptionField
     */
    public function setDescriptionField($descriptionField);

    /**
     * Return the name of the field on the mapped document which
     * represents the image URL which should accompany the search
     * result.
     *
     * @return FieldInterface
     */
    public function getImageUrlField();

    /**
     * Set the name of the field representing the URL of the image.
     *
     * @param FieldInterface $imageUrlField
     */
    public function setImageUrlField($imageUrlField);

    /**
     * Return the name of the fild on the mapped document which
     * represents the locale.
     *
     * @return FieldInterface
     */
    public function getLocaleField();

    /**
     * Set the name of the field representing the locale.
     *
     * @param FieldInterface $field
     */
    public function setLocaleField($field);

    /**
     * Get the class metadata to which this index metadata applies.
     *
     * @return ClassMetadata
     */
    public function getClassMetadata();

    /**
     * Set the class metadata to which this index applies.
     *
     * @param ClassMetadata $classMetadata
     */
    public function setClassMetadata(ClassMetadata $classMetadata);
}
