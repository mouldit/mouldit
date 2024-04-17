<?php

namespace components\Menubar;

use components\IComponent;

class Menubar extends \components\Component  implements IComponent
{
    public array $menuItems;

    public function __construct($id,$pageId,$name, $type,$path=NULL, $menuItems=NULL)
    {
        parent::__construct($id,$pageId,$name, $type,$path);
        if(isset($menuItems)) $this->menuItems=$menuItems; else $this->menuItems=[];
    }

    public function removeItem(string $name){
        for($i=0;$i<sizeof($this->menuItems);$i++){
            if($this->menuItems[$i]->name===$name){
                array_splice($this->menuItems,$i,1);
            }
        }
    }
    public function getAttributes()
    {
        return ['menuItems'];
    }


    // todo
    public function getControllerImports(){
        return ['import {MenuItem} from "primeng/api";'];
    }
    function getControllerVariables()
    {
        return ["\n".'items: MenuItem[] | undefined;'."\n"];
    }
    function getConstructorInjections()
    {
        return [];
    }



    function getImportStatement()
    {
        return [
            'import {MenubarModule} from "primeng/menubar";',
            'import {MenuModule} from "primeng/menu";'
        ];
    }
    function getImportsStatement()
    {
        return "\n".'MenubarModule,'. "\n".'MenuModule,';
    }
    function getInit($pages)
    {
        $oninit = "\n".'this.items=['."\n";
        foreach ($this->menuItems as $menuItem){
            if($menuItem->page){
                for ($i=0;$i<sizeof($pages);$i++){
                    if($pages[$i]->id===$menuItem->page){
                        $oninit.="{\t".'label:\''.$menuItem->name.'\', routerLink:\''.$pages[$i]->url.'\'},'."\n";
                        break;
                    }
                }
            } else{
                $oninit.="{\t".'label:\''.$menuItem->name.'\'},'."\n";
            }
        }
        $oninit.=']'."\n";
        return $oninit;
    }
    function getHTML(string $triggers,\Action $action=null,$ciComps=NULL)
    {
        return "<p-menubar [model]=\"items\"></p-menubar>\n";
    }
}