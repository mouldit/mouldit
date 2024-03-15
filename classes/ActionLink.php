<?php

class ActionLink
{
    public string $action;
    public int $component;

    function __construct($action,$component=NULL){
        $this->action=$action;
        if(isset($component))$this->component=$component;
    }
    function linkComponent($component){
        $this->component=$component;
    }
}