<?php

namespace components\Menubar;

use IComponent;

class Menubar extends \Component  implements IComponent
{
    public array $menuItems;

    public function __construct($id,$pageId,$name, $type, $menuItems=NULL)
    {
        parent::__construct($id,$pageId,$name, $type);
        if(isset($menuItems)) $this->menuItems=$menuItems; else $this->menuItems=[];
    }

    public function removeItem(string $name){
        for($i=0;$i<sizeof($this->menuItems);$i++){
            if($this->menuItems[$i]->name===$name){
                array_splice($this->menuItems,$i,1);
            }
        }
    }
    public function getAttributes()
    {
        return ['menuItems'];
    }


    function getImportStatement()
    {
        return "\n".'import {MenubarModule} from "primeng/menubar";'."\n".'import {MenuModule} from "primeng/menu";';
    }

    function getImportsStatement()
    {
        return "\n".'MenubarModule,'. "\n".'MenuModule,';
    }
}