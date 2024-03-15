<?php

namespace components\Menubar;

class Menubar extends \Component
{
    public array $menuItems;

    public function __construct($id,$name, $type, $menuItems=NULL)
    {
        parent::__construct($id,$name, $type);
        if(isset($menuItems)) $this->menuItems=$menuItems; else $this->menuItems=[];
    }

    public function removeItem(string $name){
        for($i=0;$i<sizeof($this->menuItems);$i++){
            if($this->menuItems[$i]->name===$name){
                array_splice($this->menuItems,$i,1);
            }
        }
    }


}