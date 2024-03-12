<?php

class ActionLink
{
    public string $action;
    public string $component;

    function __construct($action,$component=NULL){
        $this->action=$action;
        if(isset($component))$this->component=$component;
    }
    function linkComponent($name){
        $this->component=$name;
    }
}