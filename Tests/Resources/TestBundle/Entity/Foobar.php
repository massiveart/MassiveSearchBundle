<?php

namespace Massive\Bundle\MassiveSearchBundle\Tests\Resources\Entity;

class Foobar
{
    /**
     * @MassiveSearch\Field(type="string", hints={"elastica_include_in_all": true})
     */
    protected $title;

    /**
     * @MassiveSearch\Field(type="string")
     */
    protected $body;

    public function getTitle() 
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getBody() 
    {
        return $this->body;
    }
    
    public function setBody($body)
    {
        $this->body = $body;
    }
}
