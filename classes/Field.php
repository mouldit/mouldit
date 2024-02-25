<?php

class Field
{
    public string $name;
    public string $type;
    public SubFieldSet $subfields;

    public function __construct($name,$type,$subfields=NULL)
    {
        $this->name=$name;
        $this->type=$type;
        $this->subfields=$subfields;
    }

    public function addSubfield(Field $field){
        if(!isset($this->subfields)) $this->subfields=new SubFieldSet();
        $this->subfields->addSubField($field);
    }

}