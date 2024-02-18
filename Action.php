<?php

class Action
{
    public $name;
    public $active;
    public $fields;
    public $selected;
    function __construct($name){
        $this->name=$name;
        $this->active=true;
        $this->fields=[];
        $this->selected=false;
    }
    function addField($fieldname){
        $this->fields[]=$fieldname;
    }
    public function activate(): void
    {
        $this->active = true;
    }
    public function deactivate(): void
    {
        $this->active = false;
    }
}