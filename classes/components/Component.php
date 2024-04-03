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
        echo 'calling get methods';
        $methods = '';
        foreach ($this->effects as $e){
            // todo als de button de source is is er geen actionLink
            $methods.=$e->action->name.'(){';
            $methods.=$e->action->getFrontendCode($e->action->concept.'s').'}';
        }
        echo 'methods=='.$methods;
        return $methods;
    }
    protected function getTriggers(){
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

    public function removeEffect(int $id)
    {
        for ($i = 0; $i < sizeof($this->effects); $i++) {
            if ($this->effects[$i]->id === $id) {
                array_splice($this->effects, $i, 1);
                break;
            }
        }
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