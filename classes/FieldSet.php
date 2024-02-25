<?php

class FieldSet
{
    public array $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields=$fields;
    }
    public function addField(Field $field){
        $this->fields[]=$field;
    }
}