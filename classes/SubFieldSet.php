<?php

class SubFieldSet
{
    public FieldSet $subfields;

    public function __construct(FieldSet $subfields=NULL)
    {
        if($subfields)$this->subfields=$subfields; else $this->subfields=new FieldSet();
    }
    public function addSubField(Field $field){
        $this->subfields[]=$field;
    }
}