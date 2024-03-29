<?php

class Page implements IPage
{
    use FrontendMethods;
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
    public function getHTMLFilePath(){
        return  './'.$this->getPageFolderName().'.component.html';
    }
    public function getCSSFilePath(){
        return  './'.$this->getPageFolderName().'.component.css';
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
        return 'import {'.$this->getPageComponentName().'} from \''.$path.'/'.$this->getPageFolderName().'.component\';';
    }

    function getDeclarationsStatement()
    {
        return "\n{$this->getPageComponentName()},";
    }

    /**
     * @throws Exception
     */
    function getRelativeImportStatement($pages,int $nestingLevel)
    {
        $nesting = str_repeat('/..', $nestingLevel);
        $nesting=substr($nesting,1);
        return 'import {'.$this->getPageComponentName().'} from \''.$nesting.$this->getPath($pages,$this->id).'/'.$this->getPageFolderName().'.component\';';
    }
    function getRouteObj(){
        if(isset($this->parentId) && str_starts_with($this->url, '/')){
            return "{path: '".substr($this->url,1)."',component:{$this->getPageComponentName()}},\n";
        } else if(isset($this->parentId))return "{path: '".$this->url."',component:{$this->getPageComponentName()}},\n";else return "";
    }
}