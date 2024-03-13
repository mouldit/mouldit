<?php

class Component
{
    public string $name; // unique
    public string $type;
    public bool $selected;

    public function __construct($name,$type)
    {
        $this->name=$name;
        $this->type=$type;
        $this->selected=false;
    }
    public function select(){
        $this->selected=true;
    }
    public function deselect(){
        $this->selected=false;
    }


}