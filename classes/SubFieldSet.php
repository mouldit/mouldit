<?php

class SubFieldSet
{
    public FieldSet $fields;
    public function addSubField(Field $field){
        if(!isset($this->fields)) $this->fields=new FieldSet();
        $this->fields->addField($field);
    }
    public function setFields($fs){
        $this->fields=$fs;
    }
}