<?php

namespace components\Card;

use IComponent;

class Card extends \Component  implements IComponent
{
    public string $header;
    public string $subheader;

    public function __construct($id,$pageId,$name, $type,$header = NULL, $subheader=NULL)
    {
        parent::__construct($id,$pageId,$name, $type);
        if(isset($header)) $this->header=$header;
        if(isset($subheader)) $this->subheader=$subheader;
    }

    public function getAttributes()
    {
        return ['header','subheader'];
    }
}