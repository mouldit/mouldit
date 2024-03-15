<?php

namespace components\Card;

class Card extends \Component
{
    public string $title;
    public string $subtitle;

    public function __construct($id,$name, $type,$title = NULL, $subtitle=NULL)
    {
        parent::__construct($id,$name, $type);
        if(isset($title)) $this->title=$title;
        if(isset($subtitle)) $this->subtitle=$subtitle;
    }
}