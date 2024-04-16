<?php

namespace components;

use Action;
use Effect;
use Enums\TriggerType;
use FrontendMethods;

class Component
{
    use FrontendMethods;
    public readonly int $id;
    public readonly int $pageId;
    public string $name; // unique
    public string $componentPath;
    public string $type;
    public bool $selected;
    public array $mapping;
    public function __construct($id, $pageId, $name, $type, $componentPath=NULL)
    {
        $this->id = $id;
        $this->pageId = $pageId;
        $this->name = $name;
        $this->type = $type;
        $this->selected = false;
        $this->mapping = [];
        if(isset($componentPath))$this->componentPath=$componentPath;
    }
    public function removeAction(string $name){
       for($i=0;$i<sizeof($keys = array_keys($this->mapping));$i++){
           if($keys[$i]===$name){
               array_splice($this->mapping,$i,1);
               //echo '<pre>'.print_r($this->mapping, true).'</pre>';
               break;
           }
       }
    }
    public function select()
    {
        $this->selected = true;
    }

    public function deselect()
    {
        $this->selected = false;
    }

}