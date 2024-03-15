<?php

namespace components\Card;

class Card extends \Component
{
    public string $title;
    public string $subtitle;

    public function __construct($name, $type,$title = NULL, $subtitle=NULL)
    {
        parent::__construct($name, $type);
        if(isset($title)) $this->title=$title;
        if(isset($subtitle)) $this->subtitle=$subtitle;
    }
}