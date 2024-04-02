<?php

namespace components\groups;

use Enums\UnitType;

class Dimension
{
    private int $value;
    private UnitType $unit;
    private string $calculation;

    private function getDimension():string{
        if(isset($this->calculation)) return $this->calculation;
        return $this->value.$this->unit->value;
    }
    public function setDimension(): void
    {
        $args = func_get_args();
        if(sizeof($args)===1 && is_string($args[0])){
            $this->calculation = $args[0];
        } else if(sizeof($args)===2 && is_int($args[0]) && $args[1] instanceof UnitType){
            $this->value=$args[0];
            $this->unit=$args[1];
        } else throw new \Exception('Illegal dimension setting');
    }

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setDimension(...func_get_args());
    }

}