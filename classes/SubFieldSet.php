<?php

class SubFieldSet
{
    public FieldSet $fields;

    public function __construct(FieldSet $subfields=NULL)
    {
        if($subfields)$this->fields=$subfields; else $this->fields=new FieldSet();
    }
    public function addSubField(Field $field){
        $this->fields->addField($field);
    }
}