<?php

namespace components\Card;

use IComponent;

class Card extends \Component  implements IComponent
{
    public string $title;
    public string $subtitle;

    public function __construct($id,$pageId,$name, $type,$title = NULL, $subtitle=NULL)
    {
        parent::__construct($id,$pageId,$name, $type);
        if(isset($title)) $this->title=$title;
        if(isset($subtitle)) $this->subtitle=$subtitle;
    }

    public function getAttributes()
    {
        return ['title','subtitle'];
    }
}