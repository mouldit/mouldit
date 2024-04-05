<?php

class Action
{
    public string $name;
    public string $verb;
    public string $type;
    public string $concept;
    public bool $active;
    public bool $selected;
    public string $clientURL;
    public FieldSet $fieldset; // de conceptnaam komt voor als attribuut in de fieldset property

    function __construct($name, $verb, $type, $clientURL, $concept)
    {
        $this->name = $name;
        $this->verb = $verb;
        $this->type = $type;
        $this->active = true;
        $this->selected = false;
        $this->clientURL = $clientURL;
        $this->concept = $concept; // todo zoals hier is een id veel beter
    }
    public function getVariable(){
        // todo meer specifiek per type actie ipv altijd dit
        return $this->concept.'s';
    }
    function getReturnType()
    {
        if ($this->type === 'Get_all') return 'list';
    }

    function getFullQualifiedFieldNames(): array
    {
        $fullQualifiedFieldNames = [];
        $fieldsetsToProcess = [$this->fieldset];
        $newFieldsets = [];
        while (sizeof($fieldsetsToProcess) > 0) {
            foreach ($fieldsetsToProcess as $fs) {
                foreach ($fs->fields as $f) {
                    if (!$f->hasSubfields()) {
                        $fullQualifiedFieldNames[] = $f->fieldPath;
                    } else {
                        if ($fs instanceof FieldSet) $fullQualifiedFieldNames[] = $f->name;
                        $newFieldsets[] = $f->subfields;
                    }
                }
            }
            $fieldsetsToProcess = $newFieldsets;
            $newFieldsets = [];
        }
        return $fullQualifiedFieldNames;
    }

    function addField($name, $type, $checked, $subfields = NULL)
    {
        if (isset($this->fieldset)) {
            $f = new Field($name, $type, $this->fieldset);
            $f->setChecked($checked);
            if (isset($subfields)) $f->subfields = $subfields;
            $this->fieldset->addField($f);
        }
    }

    function setFields($fs)
    {
        $this->fieldset = $fs;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function select()
    {
        $this->selected = true;
    }

    public function deselect()
    {
        $this->selected = false;
    }
    public function getOnInit(string $varname=NULL){
        return 'this.http.'.$this->verb.'(\'http://localhost:5000/'
            .$this->concept.'/'.$this->concept.'s\').subscribe(res => {
            this.'.($varname ?? $this->getVariable()).')=res;
        });';
    }

/*    public function getFrontendCode(string $varname)
    {
        return 'this.http.'.$this->verb.'(\'http://localhost:5000/'
            .$this->concept.'/'.$this->concept.'s\').subscribe(res => {
            this.'.$varname.'=res;
        });';
    }*/
}