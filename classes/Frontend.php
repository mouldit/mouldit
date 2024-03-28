<?php

class Frontend
{
    public array $pages=[];

    /**
     * @throws Exception
     */
    public function getParentFor(int $pageId):Page{
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->pages[$i]->id===$pageId) return $this->pages[$i];
        }
        throw new Exception('Page not found in $pages');
    }
    public function pageExist($pageId):bool{
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->pages[$i]->id===$pageId) return true;
        }
        return false;
    }

    public function getSubPagesFor(int $pageId):array{
        $subpages = [];
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->pages[$i]->parentId===$pageId) $subpages[]=$this->pages[$i];
        }
        return $subpages;
    }

    /**
     * @throws Exception
     */
    public function getMainPage():Page{
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->isMainPage($this->pages[$i])) return $this->pages[$i];
        }
        throw new Exception('A Main Page was not found in $pages');
    }
    public function getAllResourcePages():array{
        $rp = [];
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->isResourcePage($this->pages[$i])) $rp[]=$this->pages[$i];
        }
        return $rp;
    }

    /**
     * @throws Exception
     */
    public function getPageFor($id):Page{
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->pages[$i]->id===$id) return $this->pages[$i];
        }
        throw new Exception('Page was not found in $pages');
    }
    public function getPageType($id):string{
        $p=$this->getPageFor($id);
        if($this->isMainPage($p)) return 'main page';
        if($this->isResourcePage($p)) return 'resource page';
        return 'subpage';
    }
    public function isResourcePage(Page $page):bool{
        if(!$this->pageExist($page->id) || !isset($page->parentId)) return false;
        for ($i=0;$i<sizeof($this->pages);$i++){
            if($this->pages[$i]->id===$page->parentId && $this->isMainPage($this->pages[$i])) return true;
        }
        return false;
    }
    public function isMainPage(Page $page):bool{
        return $this->pageExist($page->id) && !isset($page->parentId);
    }
    public function isSubPage(Page $page):bool{
        return $this->pageExist($page->id) && !$this->isResourcePage($page) && !$this->isMainPage($page);
    }

    /**
     * @throws Exception
     */
    public function getPath($id):string{
        $path='';
        while(isset($id) && $current = $this->getPageFor($id)){
            $path=$current->getPageFolderName().'/'.$path;
            $id=$current->parentId;
        }
        return '/'.$path;
    }

    /**
     * @throws Exception
     */
    public function generate(string $dir): void
    {
        touch($dir.'/app.component.css');
        $f = fopen($dir.'/app.component.html','wb');
        $mp = $this->getMainPage();
        $data = $mp->getHTMLSelector();
        // todo later router outlet component toevoegen aan main page by default
        $data.="\n<router-outlet/>";
        fwrite($f,$data);
        fclose($f);
        $f = fopen($dir.'/app.component.ts','wb');
        // todo toevoegen van import en andere variabelen aan de app.component.ts file
        $data=" 
import { Component } from '@angular/core';
import {{$mp->getPageComponentName()}} from \"{$mp->getPageFolderName()}/{$mp->getPageFolderName()}.component\";         
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'client';
}";
        fwrite($f,$data);
        fclose($f);
        $declared=[];
        $f = fopen($dir.'/app.module.ts','wb');
        $data = file_get_contents('/app-module.txt');
        if($data){
            foreach ($this->pages as $p){
                $data=str_replace(['COMPONENT_IMPORT_STATEMENT','COMPONENT_DECLARATIONS_STATEMENT'],
                    [$p->getImportStatement($this->getPath($p->id))."\nCOMPONENT_IMPORT_STATEMENT",
                        $p->getDeclarationsStatement()."\nCOMPONENT_DECLARATIONS_STATEMENT"],$data);
                foreach ($p->components as $c){
                    if(!in_array($c->type, $declared)){
                        $declared[]=$c->type;
                        $data = str_replace(['MODULE_IMPORT_STATEMENT','MODULE_IMPORTS_STATEMENT'],
                            [$c->getImportStatement()."\nMODULE_IMPORT_STATEMENT",$c->getImportsStatement()."\nMODULE_IMPORTS_STATEMENT"],$data);
                    }
                }
            }
            fwrite($f,$data);
            fclose($f);
        }

        // todo app.routes.ts
        // todo main page
        // todo each resourcepage
        // todo for each resource page every subpage etc.
    }
}