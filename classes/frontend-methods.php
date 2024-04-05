<?php

trait FrontendMethods
{
    /**
     * @throws Exception
     */
    public function getParentFor($pages,int $pageId): Page
    {
        for ($i = 0; $i < sizeof($pages); $i++) {
            if ($pages[$i]->id === $pageId) return $pages[$i];
        }
        throw new Exception('Page not found in $pages');
    }

    public function pageExist($pages,$pageId): bool
    {
        for ($i = 0; $i < sizeof($pages); $i++) {
            if ($pages[$i]->id === $pageId) return true;
        }
        return false;
    }

    public function getSubPagesFor($pages,int $pageId): array
    {
        $subpages = [];
        for ($i = 0; $i < sizeof($pages); $i++) {
            if ($pages[$i]->parentId === $pageId) $subpages[] = $pages[$i];
        }
        return $subpages;
    }

    /**
     * @throws Exception
     */
    public function getMainPage($pages): Page
    {
        for ($i = 0; $i < sizeof($pages); $i++) {
            if ($this->isMainPage($pages,$pages[$i])) return $pages[$i];
        }
        throw new Exception('A Main Page was not found in $pages');
    }

    public function getAllResourcePages($pages): array
    {
        $rp = [];
        for ($i = 0; $i < sizeof($pages); $i++) {
            if ($this->isResourcePage($pages,$pages[$i])) $rp[] = $pages[$i];
        }
        return $rp;
    }

    /**
     * @throws Exception
     */
    public function getPageFor($pages,$id): Page
    {
        for ($i = 1; $i <= sizeof($pages); $i++) {
            if ($pages[$i-1]->id === $id) return $pages[$i-1];
        }
        throw new Exception('Page was not found in $pages');
    }

    /**
     * @throws Exception
     */
    public function getPageType($pages, $id): string
    {
        $p = $this->getPageFor($pages,$id);
        if ($this->isMainPage($pages,$p)) return 'main page';
        if ($this->isResourcePage($pages,$p)) return 'resource page';
        return 'subpage';
    }

    public function isResourcePage($pages,Page $page): bool
    {
        if (!$this->pageExist($pages,$page->id) || !isset($page->parentId)) return false;
        for ($i = 0; $i < sizeof($pages); $i++) {
            if ($pages[$i]->id === $page->parentId && $this->isMainPage($pages,$pages[$i])) return true;
        }
        return false;
    }

    public function isMainPage($pages,Page $page): bool
    {
        return $this->pageExist($pages,$page->id) && !isset($page->parentId);
    }

    public function isSubPage($pages,Page $page): bool
    {
        return $this->pageExist($pages,$page->id) && !$this->isResourcePage($pages,$page) && !$this->isMainPage($pages,$page);
    }
    public function getLevelOfNesting(Page $page):int{
        // todo
        return 1;
    }

    /**
     * @throws Exception
     */
    public function getPath($pages,$id): string
    {
        $path = '';
        while (isset($id) && $current = $this->getPageFor($pages,$id)) {
            $path = $current->getPageFolderName() . $path;
            $id = isset($current->parentId) && !$this->isMainPage($pages,$this->getPageFor($pages,$current->parentId)) ? $current->parentId : NULL;
        }
        return '/' . $path;
    }
}