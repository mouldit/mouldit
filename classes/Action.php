<?php
class Action
{
    public string $name;
    public string $verb;
    public string $type;
    public bool $active;
    public bool $selected;
    public FieldSet $fieldset;
    function __construct($name,$verb,$type){
        $this->name=$name;
        $this->verb=$verb;
        $this->type=$type;
        $this->active=true;
        $this->selected=false;
    }
    function addField($name,$type,$checked,$subfields=NULL){
        if(!isset($this->fieldset)) $this->fieldset=new FieldSet();
        $f = new Field($name,$type);
        $f->setChecked($checked);
        if(isset($subfields)) $f->subfields=$subfields;
        $this->fieldset->addField($f);
    }
    function setFields($fs){
        $this->fieldset=$fs;
    }
    public function activate(): void
    {
        $this->active = true;
    }
    public function deactivate(): void
    {
        $this->active = false;
    }
    public function select(){
        $this->selected=true;
    }
    public function deselect(){
        $this->selected=false;
    }
}