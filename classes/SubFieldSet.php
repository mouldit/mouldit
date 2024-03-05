<?php

class SubFieldSet
{
    public array $fields;
    public bool $inclusivity;
    public string $conceptName;
    public string $conceptPath;
    public string $fieldPath;
    public function __construct(string $conceptName,string $conceptPath, string $fieldPath)
    {
        $this->conceptName=$conceptName;
        $this->fields=[];
        $this->conceptPath=$conceptPath;
        $this->fieldPath=$fieldPath;
    }
    public function __clone(){
        $temp=[];
        foreach ($this->fields as $f){
            $temp[]=clone $f;
        }
        $this->fields=$temp;
    }
    public function addSubField(Field $field){
        $this->fields[]=$field;
    }
    public function setSubFields($fields){
        $this->fields = $fields;
    }
    public function setInclusivity(bool $inc){
        $this->inclusivity=$inc;
    }
}