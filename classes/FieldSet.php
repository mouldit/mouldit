<?php

class FieldSet
{
    public array $fields;
    public string $inclusivity;

    public function __construct(array $fields)
    {
        $this->fields=$fields;
    }
    public function addField(Field $field){
        $this->fields[]=$field;
    }
    public function setInclusivity($inc){
        $this->inclusivity=$inc;
    }
}