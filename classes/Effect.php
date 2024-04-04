<?php

use Enums\TriggerType;

class Effect
{
    public int $id;
    public int $source;
    public TriggerType $trigger;
    public Action $action;
    public int $target;
    // todo conditional trigger
    // todo add Trigger class with getHTML($methodName) als methode: idd beter
    public function __construct($id,int $source,string $trigger,$action,$target)
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