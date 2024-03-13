<?php

namespace components\Menubar;

class MenuItem
{
    public string $name;
    public string $page;
    public int $number;

    public function __construct($name,$page,$number)
    {
        $this->name=$name;
        $this->page=$page;
        $this->number=$number;
    }
}