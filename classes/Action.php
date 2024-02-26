<?php
class Action
{
    public string $name;
    public string $verb;
    public string $type;
    public bool $active;
    public FieldSet $fieldset;
    function __construct($name,$verb,$type,$fieldset=NULL){
        $this->name=$name;
        $this->verb=$verb;
        $this->type=$type;
        $this->active=true;
        $this->fieldset=$fieldset;
    }
    function addField($name,$type,$checked,$subfields=NULL){
        if(!isset($this->fieldset)) $this->fieldset=new FieldSet();
        $this->fieldset->addField(new Field($name,$type,$checked,$subfields));
    }
    public function activate(): void
    {
        $this->active = true;
    }
    public function deactivate(): void
    {
        $this->active = false;
    }
}