<?php

class Page
{
    public string $name;
    public string $url;
    public ActionLink $actionLink;
    public array $components;
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
    public function linkWithAction($action,$target=NULL){
        $this->actionLink=new ActionLink($action,$target);
    }
    public function addComponent(Component $comp){
        $this->components[]=$comp;
    }
}