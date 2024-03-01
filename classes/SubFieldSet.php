<?php

class SubFieldSet
{
    public FieldSet $fields;
    public Fieldset $parentFieldset;

    public function addSubField(Field $field,$conceptName=NULL){
        if(isset($this->parentFieldset)){
            if(!isset($this->fields) && $conceptName) $this->fields=new FieldSet($conceptName);
            if(isset($this->fields)) $this->fields->addField($field);
        }
    }
    public function setFields(FieldSet $fs){
        if(isset($this->parentFieldset))$this->fields=$fs;
    }
    public function setParentFieldset(Fieldset $pfs){
        // fieldset heeft de conceptnaam
        $this->parentFieldset=$pfs;
    }
}