<?php

class Field
{
    // een veld is in theorie niet uniek
    // todo om het uniek te maken zou je kunnen kiezen het fieldset hier te vermelden waar het deel van uitmaakt
    //      of subfieldset indien het geen property is van het main concept involved
    public string $name;
    public string $type;
    public bool $checked;
    public SubFieldSet $subfields;
    public SubFieldSet $parentSubFieldSet;
    public FieldSet $parentFieldSet;
    public function __construct($name,$type)
    {
        $this->name=$name;
        $this->type=$type;
    }
    public function setParentFieldSet($fs){
        $this->parentFieldSet=$fs;
    }
    public function setParentSubFieldSet($sfs){
        $this->parentSubFieldSet=$sfs;
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