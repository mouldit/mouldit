<?php

class FieldSet
{
    public array $fields; // bij een gewone clone ga je hier referenties hebben naar de Fiel instanties van de gekloonde Fieldset
    public bool $inclusivity;
    public string $conceptName;

    public function __construct(string $conceptName)
    {
        $this->conceptName=$conceptName;
        $this->fields=[];
    }
    public function __clone(){
        $temp=[];
        foreach ($this->fields as $f){
            $temp[]=clone $f;
        }
        $this->fields=$temp;
    }
    public function addField(Field $field){
        $this->fields[]=$field;
    }
    public function addFields($fields){
        $this->fields = $fields;
    }
    public function setInclusivity(bool $inc){
        $this->inclusivity=$inc;
    }
}