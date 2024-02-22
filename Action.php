<?php
class Action
{
    public $name;
    public $verb;
    public $action;
    public $active;
    public $fields;
    public $selected;
    function __construct($name,$verb,$action){
        $this->name=$name;
        $this->verb=$verb;
        $this->action=$action;
        $this->active=true;
        $this->fields=[];
        $this->selected=false;
    }
    function addField($fieldname,$config,$checked){
        $this->fields[]=[$fieldname,$config,$checked];
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