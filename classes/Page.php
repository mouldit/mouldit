<?php

class Page implements IPage
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
    public function getHTMLSelector(){
        $fn = $this->getPageFolderName();
        return '<app-'.$fn.'></app-'.$fn.'>';
    }
    function getPageComponentName(){
        $componentName = explode('_',$this->name);
        $componentName = array_slice($componentName,-2);
        array_walk($componentName,function (&$el,$index){
            $el = ucfirst($el);
        });
        return implode('',$componentName).'Component';
    }
    function getPageFolderName(): string
    {
        $folderName = explode('_',$this->name);
        $folderName = array_slice($folderName,-2);
        return implode('-',$folderName);
    }

    public function addComponent(Component $comp){
        $this->components[]=$comp;
    }
    function getImportStatement(string $path)
    {
        return 'import {'.$this->getPageComponentName().'} from \''.$path.$this->getPageFolderName().'.component\';';
    }

    function getDeclarationsStatement()
    {
        return "\n{$this->getPageComponentName()},";
    }
}