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

use Metadata\MethodMetadata;
use Metadata\PropertyMetadata;

/**
 * Metadata for searchable objects
 * @package Massive\Bundle\SearchBundle\Search\Metadata
 */
interface IndexMetadataInterface
{
    public function setUrlField($urlField);

    public function setDescriptionField($descriptionField);

    public function getFieldMapping();

    public function setTitleField($titleField);

    public function getUrlField();

    public function getIdField();

    public function setIndexName($indexName);

    public function addFieldMapping($name, $mapping);

    public function setFieldMapping($fieldMapping);

    public function getTitleField();

    public function getIndexName();

    public function setIdField($idField);

    public function getDescriptionField();
}
