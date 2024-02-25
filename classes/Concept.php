<?php

class Concept
{
    public string $name;
    public string $type;
    public FieldSet $fields;
    public string $includeOrExclude;
    public function __construct($name,$type,$fields=NULL,$includeOrExclude=NULL)
    {
        $this->name=$name;
        $this->type=$type;
        $this->fields=$fields;
        $this->includeOrExclude=$includeOrExclude;
    }
    public function addField(Field $field){
        if(!isset($this->fields)) $this->fields = new FieldSet();
        $this->fields->addField($field);
    }
}