<?php

class Page
{
    public string $name;
    public string $url;
    public string $action;
    public bool $selected;
    function __construct($name,$url)
    {
        $this->name=$name;
        $this->url=$url;
        $this->selected=false;
    }
    public function select(){
        $this->selected=true;
    }
    public function deselect(){
        $this->selected=false;
    }
    public function linkWithAction($action){
        $this->action=$action;
    }
}