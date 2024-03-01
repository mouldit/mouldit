<?php

class Field
{
    public string $name;
    public string $type;
    public bool $checked;
    public SubFieldSet $subfields;
    public function __construct($name,$type)
    {
        $this->name=$name;
        $this->type=$type;
    }
    public function addSubfield(Field $field){
        if(isset($this->subfields)){
            $this->subfields->addSubField($field);
        }
    }
    public function setSubFields(SubFieldSet $fieldSet){
        $this->subfields=$fieldSet;
    }
    public function setChecked($checked){
        $this->checked=$checked;
    }
    public function hasSubfields(){
        return isset($this->subfields->fields->fields) && sizeof($this->subfields->fields->fields)>0;
    }

}