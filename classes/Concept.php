<?php

class Concept
{
    public string $name;
    public string $type;
    public FieldSet $fields;
    public function __construct($name,$type)
    {
        $this->name=$name;
        $this->type=$type;
        $this->fields=new FieldSet($this->name);
    }
    public function addField(Field $field){
        $this->fields->addField($field);
    }
    public function addFields(array $fields){
        foreach ($fields as $f) {
            $this->fields->addField($f);
        }
    }
    public function setFields($fs){
        $this->fields=$fs;
    }
}