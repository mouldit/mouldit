<?php

class Action
{
    public string $name;
    public string $verb;

    public string $type;
    public string $concept;
    public string $fieldName;
    public string $fieldType;
    public bool $active;
    public bool $selected;
    public string $clientURL;
    public string $serverURL;
    public FieldSet $fieldset; // de conceptnaam komt voor als attribuut in de fieldset property

    function __construct($name, $verb, $type, $clientURL, $concept,$url,$fieldName=NULL,$fieldType=NULL)
    {
        $this->name = $name;
        $this->verb = $verb;
        $this->type = $type;
        $this->active = true;
        $this->selected = false;
        $this->clientURL = $clientURL;
        $this->serverURL=$url;
        $this->concept = $concept; // todo zoals hier is een id veel beter!!!
        if(isset($fieldName))$this->fieldName = $fieldName;
        if(isset($fieldType))$this->fieldType = $fieldType;
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
    public function isAsynchronous(){
        return true; // todo net zoals er verschillende soorten triggers bestaan gaan er verschillende soorten actions moeten komen
    }
    public function getAsJavaScript(bool $id,Effect $e){
        return
            'this.http.'.$this->verb.'(\'http://localhost:5000/'
            // todo replace :id's!
            .$this->serverURL.'\').subscribe(res => {'
            .'this.triggerService.'.lcfirst($e->trigger->name)
            .ucfirst($e->source->name).($id?'_'.$e->source->id:'').'.emit('
            .'res'
            .');'."\n});";
    }
    public function getAction(){
        return   'this.http.'.$this->verb.'(\'http://localhost:5000/'
            // todo replace :id's!
            // 'http://localhost:5000/account/remove/form/watchlist/'+this.accountId+'/'+contentId
            // de id's komen als params in de method
            /*
             *       <p-button
        (click)="onClickGet_all_contents_page_button_component_1(accountId,content.id)" label="get movies" icon="pi pi-filter-slash"
        iconPos="left">
      </p-button>
            HOE weet de engine dat er parameters nodig zijn uberhaupt? aan de hand van de actie: bij add or remove of aan de hand van de url :contentId
            todo: return value voor nested components
             * */
            // todo fix bug: serverURL wordt pas ingevuld bij generate backend ... wat als je eerst de frontend wil doen?
            .$this->serverURL.'\').subscribe(res => {'
            .'this.'.$this->getVariable().'=res'."\n});";
    }
}
