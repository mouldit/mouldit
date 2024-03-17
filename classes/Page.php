<?php

class Page
{
    public readonly int $id; // statisch
    public string $name; // wijzigt
    public string $url; // wijzigt
    public array $components; // wijzigt
    public bool $selected; // wijzigt
    public bool $main; // wijzigt
    public string $actionLink;
    function __construct($id,$name,$url,$main=NULL)
    {
        $this->id=$id;
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

    public function addComponent(Component $comp){
        $this->components[]=$comp;
    }
}