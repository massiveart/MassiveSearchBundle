<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

/**
 * Representation of a one indexed document
 */
class Document implements \JsonSerializable
{
    /**
     * @var Field[]
     */
    protected $fields = array();

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $imageUrl;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param Field $field
     */
    public function addField(Field $field)
    {
        if ($this->hasField($field->getName())) {
            throw new \InvalidArgumentException(sprintf(
                'Field "%s" already exists in search document',
                $field->getName()
            ));
        }

        $this->fields[$field->getName()] = $field;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Return the URL for the image which should be
     * displayed with this search result.
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set the URL to the image which should be displayed
     * with this search result.
     *
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Return the named field
     *
     * @return Field
     * @throws InvalidArgumentException When it doesn't exist
     */
    public function getField($name)
    {
        if (!isset($this->fields[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Trying to get undefined field "%s", defined fields are "%s"',
                $name, implode(', ', array_keys($this->fields))
            ));
        }

        return $this->fields[$name];
    }

    /**
     * Return true if the field exists
     *
     * @return boolean
     */
    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'class' => $this->class,
            'url' => $this->url,
            'image_url' => $this->imageUrl,
            'locale' => $this->locale,
        );
    }
}
