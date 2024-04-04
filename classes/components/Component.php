<?php

namespace components;

use Action;
use Effect;
use Enums\TriggerType;
use FrontendMethods;

class Component
{
    use FrontendMethods;

    public readonly int $id;
    public readonly int $pageId;
    public string $name; // unique
    public string $type;
    public Action $actionLink; //wijzigt
    public array $effects;
    public bool $selected;
    public array $mapping;

    public function __construct($id, $pageId, $name, $type)
    {
        $this->id = $id;
        $this->pageId = $pageId;
        $this->name = $name;
        $this->type = $type;
        $this->selected = false;
        $this->mapping = [];
        $this->effects = [];
    }

    public function getMethods()
    {
        // TS: todo deze code moet anders: is gewoon de trigger service callen nu al dan niet met data
        //     todo on page load trigger ... ????
        $methods = '';
        foreach ($this->effects as $e){
            $methods.=$e->action->name.'(){';
            // todo deze code zal in het target komen in de oninit per definitie
            $methods.=$e->action->getFrontendCode($e->action->concept.'s').'}';
        }
        return $methods;
    }
    protected function getTriggers(){
        // HTML
        $methods='';
        foreach ($this->effects as $e){
            $methods.=$e->trigger->value.'="'.$e->action->name.'()"';
        }
        return $methods;
    }

    public function addEffect(Effect $e)
    {
        $this->effects[] = $e;
    }



    public function select()
    {
        $this->selected = true;
    }

    public function deselect()
    {
        $this->selected = false;
    }

    public function linkWithAction($action)
    {
        $this->actionLink = $action;
    }

}