<?php

use Enums\TriggerType;

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
        // todo import van de TriggerService/Aanmaken van de TriggerService
        // todo bundel actions bij eenzelfde trigger + source
        if($this->trigger instanceof TriggerType) return lcfirst($this->trigger->name)
            .ucfirst($this->source->name).'(){'."\n\t\t"
            .'this.triggerService.'.lcfirst($this->trigger->name)
            .ucfirst($this->source->name).($id?'_'.$this->source->id:'').'.next();'."\n}\n"; else return '';
    }
    public function getOnInit(){
        // todo bundel actions bij eenzelfde trigger + source
        $onInit ='';
        if($this->trigger instanceof \Enums\PageTriggerType){
            $onInit.=$this->action->getOnInit();
        } else{
            $onInit.='this.triggerService.'
                .lcfirst($this->trigger->name)
                .ucfirst($this->source->name)
                .'.subscribe(res=>{'
                ."\n"
                .$this->action->getOnInit()
                .'});'
                ."\n";
        }
    }

    public function __construct($id,\components\Component $source,string $trigger,$action,\components\Component $target)
    {
        $this->id=$id;
        $this->source = $source;
        $this->target=$target;
        $arr = TriggerType::cases();
        for ($i=0;$i<sizeof($arr);$i++){
            if($arr[$i]->name===$trigger){
                $this->trigger=$arr[$i];
                break;
            }
        }
        $this->action=$action;
    }

}