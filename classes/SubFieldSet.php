<?php

class SubFieldSet
{
    public FieldSet $fields;

    public function addSubField(Field $field,$conceptName=NULL){
            if(!isset($this->fields) && $conceptName) $this->fields=new FieldSet($conceptName);
            if(isset($this->fields)) $this->fields->addField($field);
    }
    public function setFields(FieldSet $fs){
        $this->fields=$fs;
    }
}