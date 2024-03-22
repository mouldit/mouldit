<?php

class Page
{
    // todo parent id zodat de compiler weet dat een bepaalde pagina als subpagina in angular geprint moet worden (=subfolder)
    //      indien er geen parent id is dan is dit een main resource page, typisch voor get all RESOURCENAME actionpages
    //      standaard zal een getByID actie een subpage zijn van een get all resource main page
    //      idem voor update by id pages
    public readonly int $id; // statisch
    public string $name; // wijzigt
    public int $parentId;
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
    public function setParentId($id){
        $this->parentId=$id;
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