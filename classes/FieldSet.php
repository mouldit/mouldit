<?php

class FieldSet
{
    public array $fields;
    public string $inclusivity;
    public string $conceptName;

    public function __construct(string $conceptName)
    {
        $this->conceptName=$conceptName;
        $this->fields=[];
    }
    public function addField(Field $field){
        $this->fields[]=$field;
    }
    public function addFields($fields){
        $this->fields = $fields;
    }
    public function setInclusivity($inc){
        $this->inclusivity=$inc;
    }
}