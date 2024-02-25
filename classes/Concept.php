<?php

class Concept
{
    public string $name;
    public string $type;
    public FieldSet $fields;
    public function __construct($name,$type,$fields=NULL,$includeOrExclude=NULL)
    {
        $this->name=$name;
        $this->type=$type;
        $this->fields=$fields;
    }
    public function addField(Field $field){
        if(!isset($this->fields)) $this->fields = new FieldSet();
        $this->fields->addField($field);
    }
}