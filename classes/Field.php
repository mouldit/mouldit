<?php

class Field
{
    public string $name;
    public string $type;
    public bool $checked;
    public string $conceptName;
    public SubFieldSet $subfields;
    public function __construct($name,$type,$concept)
    {
        $this->name=$name;
        $this->type=$type;
        $this->conceptName=$concept;
    }
    public function addSubfield(Field $field){
        if(isset($this->subfields)){
            $this->subfields->addSubField($field);
        }
    }
    public function setSubFields(SubFieldSet $fieldSet){
        $this->subfields=$fieldSet;
    }
    public function setChecked(bool $checked){
        $this->checked=$checked;
    }
    public function hasSubfields(){
        // todo controleer method
        return isset($this->subfields->fields) && sizeof($this->subfields->fields)>0;
    }

}