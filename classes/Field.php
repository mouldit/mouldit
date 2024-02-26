<?php

class Field
{
    public string $name;
    public string $type;
    public bool $checked;
    public SubFieldSet $subfields;
    public function __construct($name,$type,$checked=NULL,$subfields=NULL)
    {
        $this->name=$name;
        $this->type=$type;
        $this->checked=$checked;
        $this->subfields=$subfields;
    }

    public function addSubfield(Field $field){
        if(!isset($this->subfields)) $this->subfields=new SubFieldSet();
        $this->subfields->addSubField($field);
    }

    public function setChecked($checked){
        $this->checked=$checked;
    }

}