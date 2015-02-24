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

/**
 * Metadata for searchable objects
 * @package Massive\Bundle\SearchBundle\Search\Metadata
 */
interface IndexMetadataInterface
{
    /**
     * The FQCN of the mapped class this index represents
     *
     * @return string
     */
    public function getName();

    /**
     * Set the FCQN of the class this index represents
     *
     * @param string
     */
    public function setName($name);

    /**
     * Set the name of the field representing the URL
     *
     * @param string
     */
    public function setUrlField($urlField);

    /**
     * Set the name of the field representing the description
     *
     * @param string
     */
    public function setDescriptionField($descriptionField);

    /**
     * Return the field mapping
     *
     * @return array
     */
    public function getFieldMapping();

    /**
     * Set the name of the field representing the title
     *
     * @param string
     */
    public function setTitleField($titleField);

    /**
     * Return the name of the field on the mapped document which
     * represents the URL (should be named URI)
     *
     * @return string
     */
    public function getUrlField();

    /**
     * Return the name of the field on the mapped document which
     * represents the ID
     *
     * @return string
     */
    public function getIdField();

    /**
     * Set the search index name
     */
    public function setIndexName($indexName);

    /**
     * Add a field mapping
     *
     * @param string
     * @param array
     */
    public function addFieldMapping($name, $mapping);

    /**
     * Set the field mapping
     *
     * @param array
     */
    public function setFieldMapping($fieldMapping);

    /**
     * Return the name of the field on the mapped document which
     * represents the ID
     *
     * @return string
     */
    public function getTitleField();

    /**
     * Return the search index name to use for the mapped class
     *
     * @return string
     */
    public function getIndexName();

    /**
     * Set the name of the field on the mapped document which represents
     * the ID field
     *
     * @param string
     */
    public function setIdField($idField);

    /**
     * Return the name of the field on the mapped document which
     * represents the description
     *
     * @return string
     */
    public function getDescriptionField();

    /**
     * Return the name of the field on the mapped document which
     * represents the image URL which should accompany the search
     * result.
     *
     * @return string
     */
    public function getImageUrlField();

    /**
     * Set the name of the field representing the URL of the image
     *
     * @param string
     */
    public function setImageUrlField($imageUrlField);

    /**
     * Return the name of the fild on the mapped document which
     * represents the locale
     *
     * @return string
     */
    public function getLocaleField();

    /**
     * Set the name of the field representing the locale
     *
     * @param string
     */
    public function setLocaleField($field);

    /**
     * Get the class metadata to which this index metadata applies
     *
     * @return ClassMetadata
     */
    public function getClassMetadata();

    /**
     * Set the class metadata to which this index applies
     *
     * @param ClassMetadata $classMetadata
     */
    public function setClassMetadata(ClassMetadata $classMetadata);
}
