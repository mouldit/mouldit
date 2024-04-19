<?php

use components\Component;

class Page implements IPage
{
    use FrontendMethods;

    public readonly int $id; // statisch
    public string $name; // wijzigt
    public int $parentId;
    public string $url; // wijzigt
    public array $components; // wijzigt
    public bool $selected; // wijzigt
    public bool $main; // wijzigt
    public array $imports;
    public array $constructorInjections;
    public array $controllerVariables;

    function __construct($id, $name, $url, $main = NULL)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->selected = false;
        $this->components = [];
        $this->imports=[];
        $this->constructorInjections=[];
        $this->controllerVariables=[];
        if (isset($main)) $this->main = $main; else $this->main = false;
    }
    public function createViewController(&$data, array $effects, array $pages)
    {
        $effectOnInit = '';
        $effectMethods = '';
        $effectCstr = '';
        $effectImports = '';
        $effectVars = '';
        $onInit='';
        $lon = $this->getLevelOfNesting($this);
        foreach ($this->components as $c) {
            $this->constructorInjections = array_unique(array_merge($this->constructorInjections, $c->getConstructorInjections()));
            $this->controllerVariables = array_unique(array_merge($this->controllerVariables, $c->getcontrollerVariables()));
            $this->imports = array_unique(array_merge($this->imports, $c->getControllerImports()));
            foreach ($effects as $e) {
                if ($e->source->id === $c->id) {
                    if (!str_contains($effectMethods, $e->getMethods(false))) {
                        $effectMethods .= "\n{$e->getMethods(false)}";
                    }
                    if (!str_contains($effectImports, $e->getImports($lon))) {
                        $effectImports .= "\n{$e->getImports($lon)}";
                    }
                }
                if ($e->target->id === $c->id) {
                    if (!str_contains($effectOnInit, $e->getOnInit(false))) {
                        $effectOnInit .= "\n{$e->getOnInit(false)}";
                    }
                    if (!str_contains($effectImports, $e->getImports($lon))) {
                        $effectImports .= "\n{$e->getImports($lon)}";
                    }
                    if (!str_contains($effectCstr, $e->getConstructorVariables())) {
                        $effectCstr .= "\n{$e->getConstructorVariables()}";
                    }
                    if (!str_contains($effectVars, $e->getVariable())) {
                        $effectVars .= "\n{$e->getVariable()}";
                    }
                }
                if (!str_contains($onInit, $c->getInit($pages))) {
                    $onInit .= "\n{$c->getInit($pages)}";
                }
            }
        }
        $data = str_replace([
            'COMPONENT_IMPORT_STATEMENT',
            'COMPONENT_VARIABLES',
            'NG_ON_INIT_BODY',
            'CONSTRUCTOR_VARIABLES',
            'COMPONENT_METHODS'
        ], [implode("\n", $this->imports) . $effectImports . "\nCOMPONENT_IMPORT_STATEMENT",
            implode("\n", $this->controllerVariables) . $effectVars . "\nCOMPONENT_VARIABLES",
            $onInit . $effectOnInit . "\nNG_ON_INIT_BODY",
            implode("\n", $this->constructorInjections) . $effectCstr . "\nCONSTRUCTOR_VARIABLES",
            $effectMethods . "\nCOMPONENT_METHODS"
        ],
            $data);
    }

    public function setParentId($id)
    {
        $this->parentId = $id;
    }
    public function getPageComponent(int $source){
        for ($i=0;$i<sizeof($this->components);$i++){
            if($this->components[$i]->id===$source) return $this->components[$i];
            if(isset($this->components[$i]->ci->contentInjection)){
                $arr = array_values($this->components[$i]->ci->contentInjection);
                foreach ($arr as $v){
                    if(isset($v) && $v->id===$source){
                        // todo maak hier een echte nesting van
                        return $v;
                    }
                }
            }
        }
    }
    public function getNestedComponents($id=null){
        $nested = [];
        if(isset($id)){
            for ($i=0;$i<sizeof($this->components);$i++){
                if(isset($this->components[$i]->ci->contentInjection)&&$this->components[$i]->id===$id){
                    $arr = array_values($this->components[$i]->ci->contentInjection);
                    foreach ($arr as $v){
                        if(isset($v))$nested[]=$v;
                    }
                }
            }
        } else{
            for ($i=0;$i<sizeof($this->components);$i++){
                if(isset($this->components[$i]->ci->contentInjection)){
                    $arr = array_values($this->components[$i]->ci->contentInjection);
                    foreach ($arr as $v){
                        if(isset($v))$nested[]=$v;
                    }
                }
            }
        }
        return $nested;
    }

    public function select()
    {
        $this->selected = true;
    }

    public function deselect()
    {
        $this->selected = false;
    }

    public function getHTMLSelector()
    {
        $fn = $this->getPageFolderName();
        return '<app-' . $fn . '></app-' . $fn . '>';
    }

    public function getHTMLFilePath()
    {
        return './' . $this->getPageFolderName() . '.component.html';
    }

    public function getCSSFilePath()
    {
        return './' . $this->getPageFolderName() . '.component.css';
    }

    function getPageComponentName()
    {
        $componentName = explode('_', $this->name);
        $componentName = array_slice($componentName, -2);
        array_walk($componentName, function (&$el, $index) {
            $el = ucfirst($el);
        });
        return implode('', $componentName) . 'Component';
    }

    function getPageFolderName(): string
    {
        $folderName = explode('_', $this->name);
        $folderName = array_slice($folderName, -2);
        return implode('-', $folderName);
    }

    public function addComponent(Component $comp)
    {
        $this->components[] = $comp;
    }

    function getImportStatement(string $path)
    {
        return 'import {' . $this->getPageComponentName() . '} from \'' . $path . '/' . $this->getPageFolderName() . '.component\';';
    }

    function getDeclarationsStatement()
    {
        return "\n{$this->getPageComponentName()},";
    }

    /**
     * @throws Exception
     */
    function getRelativeImportStatement($pages, int $nestingLevel)
    {
        $nesting = str_repeat('/..', $nestingLevel);
        $nesting = substr($nesting, 1);
        return 'import {' . $this->getPageComponentName() . '} from \'' . $nesting . $this->getPath($pages, $this->id) . '/' . $this->getPageFolderName() . '.component\';';
    }

    function getRouteObj()
    {
        if (isset($this->parentId) && str_starts_with($this->url, '/')) {
            return "{path: '" . substr($this->url, 1) . "',component:{$this->getPageComponentName()}},\n";
        } else if (isset($this->parentId)) return "{path: '" . $this->url . "',component:{$this->getPageComponentName()}},\n"; else return "";
    }
}