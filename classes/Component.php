<?php

class Component
{
    public readonly int $id;
    public string $name; // unique
    public string $type;
    public Action $actionLink; //wijzigt
    public bool $selected;

    public function __construct($id,$name,$type)
    {
        $this->id=$id;
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
    public function linkWithAction($action){
        $this->actionLink=$action;
    }

}