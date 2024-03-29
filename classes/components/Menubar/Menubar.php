<?php

namespace components\Menubar;

use IComponent;

class Menubar extends \Component  implements IComponent
{
    public array $menuItems;

    public function __construct($id,$pageId,$name, $type, $menuItems=NULL)
    {
        parent::__construct($id,$pageId,$name, $type);
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


    function getImportStatement()
    {
        return "\n".'import {MenubarModule} from "primeng/menubar";'."\n".'import {MenuModule} from "primeng/menu";';
    }

    function getImportsStatement()
    {
        return "\n".'MenubarModule,'. "\n".'MenuModule,';
    }

    function getComponentImportStatements(int $levelsOfNesting,array $pages):string
    {
        echo 'hi from '.$this->name;
        $importStatements = '';
        foreach ($this->menuItems as $mi){
            // todo fix: je beschikt wel over de methodes maar niet over de waarde pages!
            for ($i=0;$i<sizeof($pages);$i++){
                if($pages[$i]->id===$mi->page){
                    $importStatements.="\n".$pages[$i]->getRelativeImportStatement($pages,$levelsOfNesting);
                    break;
                }
            }
        }
        return $importStatements;
    }

    function getVariables()
    {
        return ["\n".'items: MenuItem[] | undefined;'."\n",['import {MenuItem} from "primeng/api";']];
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

    function getConstructor()
    {
        return '';
    }

    function getHTML()
    {
        return "<p-menubar [model]=\"items\"></p-menubar>\n";
    }
}