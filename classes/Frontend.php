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
}