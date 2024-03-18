<?php

class Component
{
    public readonly int $id;
    public readonly int $pageId;
    public string $name; // unique
    public string $type;
    public Action $actionLink; //wijzigt
    public bool $selected;
    public array $mapping;

    public function __construct($id,$pageId,$name,$type)
    {
        $this->id=$id;
        $this->pageId = $pageId;
        $this->name=$name;
        $this->type=$type;
        $this->selected=false;
        $this->mapping = [];
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