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
    // todo sommige componenten maken gebruik van pagina componenten die dan ook geïmporteerd worden
    function getImportStatement()
    {
        return "\n".'import {CardModule} from "primeng/card";';
    }

    function getImportsStatement()
    {
        return "\n".'CardModule,';
    }

    function getComponentImportStatements( int $levelsOfNesting)
    {
        return '';
    }
}