<?php

class Component
{
    public string $name; // unique
    public string $type;

    public function __construct($name,$type)
    {
        $this->name=$name;
        $this->type=$type;
    }


}