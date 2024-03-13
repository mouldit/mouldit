<?php

class Page
{
    public string $name;
    public string $url;
    public ActionLink $actionLink;
    public array $components;
    public bool $selected;
    public bool $main;
    function __construct($name,$url,$main=NULL)
    {
        $this->name=$name;
        $this->url=$url;
        $this->selected=false;
        $this->components=[];
        if(isset($main)) $this->main=$main; else $this->main=false;
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