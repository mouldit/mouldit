<?php

namespace components\Menubar;

class MenuItem
{
    public string $name;
    public int $page;
    public int $number;

    public function __construct($name,$pageId,$number)
    {
        $this->name=$name;
        $this->page=$pageId;
        $this->number=$number;
    }
}