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

    // todo controleer of in de service de variabelen van een on page load wel worden aangemaakt

    // todo onderstaande methods aanpassen naar correcte trigger/action structuur
    public function getTrigger(){
        if($this->trigger instanceof TriggerType) return $this->trigger->value
            .'="'
            .lcfirst($this->trigger->name)
            .ucfirst($this->source->name).'()"'; else return '';
    }
    public function getMethods(bool $id){
        // todo bundel actions bij eenzelfde trigger + source
        if($this->trigger instanceof TriggerType) return lcfirst($this->trigger->name)
            .ucfirst($this->source->name).'(){'."\n\t\t"
            .$this->action->getAsJavaScript($id,$this)."\n".'}'; else return '';
    }
    public function getOnInit(bool $id):string{
        // hier gaat de "target pagina" in de oninit method subscribe op de waarde van het result van de trigger service variabele
        // todo bundel actions bij eenzelfde trigger + source
        $onInit ='';
        if($this->trigger instanceof PageTriggerType){
            $onInit.=$this->action->getAction();
        }
        if($this->action->isAsynchronous()&&!($this->trigger instanceof PageTriggerType)){
            $onInit.='this.triggerService.'.lcfirst($this->trigger->name)
            .ucfirst($this->source->name).($id?'_'.$this->source->id:'').'.subscribe((res: any)=>{
            '."\nthis.".$this->action->getVariable()."=res;\n".'
            });'."\n";;
        }
        return $onInit;
    }
    public function getImports(int $levelOfNesting=null){
        if($this->action->isAsynchronous()){
            if(isset($levelOfNesting)){
                return 'import {TriggerService} from "'. str_repeat('../',$levelOfNesting).'services/trigger-service";'."\n";
            } else return  'import {TriggerService} from "./services/trigger-service";'."\n";
        } else return '';
    }
    public function getVariable(){
        return $this->action->getVariable().':any';
    }
    public function getConstructorVariables(){
        if($this->action->isAsynchronous()) return "private triggerService:TriggerService, "; else return '';
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