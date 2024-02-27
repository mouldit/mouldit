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
    }
    public function addField(Field $field){
        if(!isset($this->fields)) $this->fields = new FieldSet();
        $this->fields->addField($field);
    }
    public function addFields(array $fields){
        if(!isset($this->fields)){
            $this->fields = new FieldSet($fields);
        } else{
            foreach ($fields as $f){
                $this->fields->addField($f);
            }
        }
    }
    public function setFields($fs){
        $this->fields=$fs;
    }
}