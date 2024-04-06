<?php

use Enums\TriggerType;
use Enums\PageTriggerType;

class Effect
{
    public int $id;
    public readonly \components\Component $source;
    public mixed $trigger;
    public Action $action;
    public readonly \components\Component $target;
    // todo conditional trigger
    public function getHTML(){
        // het betreft de html toe te voegen aan de tag van de overeenkomstige source component
        if($this->trigger instanceof TriggerType) return $this->trigger->value
            .'="'
            .lcfirst($this->trigger->name)
            .ucfirst($this->source->name).'()"'; else return '';
    }
    public function getMethods(bool $id){
        // todo import van de TriggerService + aanmaken in constrcutor
        // todo bundel actions bij eenzelfde trigger + source
        if($this->trigger instanceof TriggerType) return lcfirst($this->trigger->name)
            .ucfirst($this->source->name).'(){'."\n\t\t"
            .'this.triggerService.'.lcfirst($this->trigger->name)
            .ucfirst($this->source->name).($id?'_'.$this->source->id:'').'.emit();'."\n}\n"; else return '';
    }
    public function getOnInit(bool $action=false, bool $id=false):string{
        // todo bundel actions bij eenzelfde trigger + source
        $onInit ='';
        if($this->trigger instanceof PageTriggerType||($action && $this->action->isAsynchronous())){
            $onInit.=$this->action->getOnInit();
        }
        if($action && !$this->action->isAsynchronous()){
            $onInit.='this.triggerService.'.lcfirst($this->trigger->name)
            .ucfirst($this->source->name).($id?'_'.$this->source->id:'').'.subscribe(res=>{
            '.$this->action->getOnInit()."\n".'
            });'."\n}\n";;
        }
        return $onInit;
    }
    public function getImports(){
        // todo
    }
    public function getConstructorVariables(){
        // todo
    }
    public function __construct($id,\components\Component $source,string $trigger,$action,\components\Component $target)
    {
        $this->id=$id;
        $this->source = $source;
        $this->target=$target;
        $triggers = \Enums\TriggerType::cases();
        $pageTriggers = \Enums\PageTriggerType::cases();
        $arr=array_merge($triggers,$pageTriggers);
        for ($i=0;$i<sizeof($arr);$i++){
            if($arr[$i]->name===$trigger){
                $this->trigger=$arr[$i];
                break;
            }
        }
        $this->action=$action;
    }

}