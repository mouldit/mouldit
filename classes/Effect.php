<?php

use Enums\TriggerType;

class Effect
{
    public int $id;
    public TriggerType $trigger;
    public Action $action;
    public int $target;
    // todo conditional trigger
    public function __construct($id,string $trigger,$action,$target)
    {
        $this->id=$id;
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