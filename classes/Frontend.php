<?php

class Frontend
{
    use FrontendMethods;
    public array $pages = [];
    public array $effects = [];
    public function removeEffect(int $id)
    {
        for ($i = 0; $i < sizeof($this->effects); $i++) {
            if ($this->effects[$i]->id === $id) {
                array_splice($this->effects, $i, 1);
                break;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function generate(string $dir): void
    {
        touch($dir . '/app.component.css');
        $f = fopen($dir . '/app.component.html', 'wb');
        $mp = $this->getMainPage($this->pages);
        $data = $mp->getHTMLSelector();
        // todo later router outlet component toevoegen aan main page by default
        $data .= "\n<router-outlet/>";
        fwrite($f, $data);
        fclose($f);
        $f = fopen($dir . '/app.component.ts', 'wb');
        // todo toevoegen van import en andere variabelen aan de app.component.ts file
$data = "import { Component } from '@angular/core';    
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'client';
}";
        fwrite($f, $data);
        fclose($f);
        $declared = [];
        $f = fopen($dir . '/app.module.ts', 'wb');
        $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/text-files/app-module.txt');
        if ($f && $data) {
            $data = str_replace(['COMPONENT_IMPORT_STATEMENT','MODULE_IMPORTS_STATEMENT', 'MODULE_PROVIDER_STATEMENT'],
                ["\nimport { HttpClientModule } from '@angular/common/http';\nCOMPONENT_IMPORT_STATEMENT",
                    "\nHttpClientModule,\nMODULE_IMPORTS_STATEMENT",""], $data);
            foreach ($this->pages as $p) {
                $data = str_replace(['COMPONENT_IMPORT_STATEMENT', 'COMPONENT_DECLARATIONS_STATEMENT','MODULE_PROVIDER_STATEMENT'],
                    [$p->getImportStatement('.'.$this->getPath($this->pages,$p->id)) . "\nCOMPONENT_IMPORT_STATEMENT",
                       $p->getDeclarationsStatement() . "\nCOMPONENT_DECLARATIONS_STATEMENT",""], $data);
                foreach ($p->components as $c) {
                    if (!in_array($c->type, $declared)) {
                        $declared[] = $c->type;
                        $data = str_replace(['MODULE_IMPORT_STATEMENT', 'MODULE_IMPORTS_STATEMENT'],
                            [$c->getImportStatement() . "\nMODULE_IMPORT_STATEMENT", $c->getImportsStatement() . "\nMODULE_IMPORTS_STATEMENT"], $data);
                    }
                }
            }
            $data = str_replace(['MODULE_IMPORT_STATEMENT', 'MODULE_IMPORTS_STATEMENT', 'COMPONENT_IMPORT_STATEMENT', 'COMPONENT_DECLARATIONS_STATEMENT'],
                ['', '', '', ''], $data);
            fwrite($f, $data);
        }
        if ($f) fclose($f);
        $f = fopen($dir . '/app-routing.module.ts', 'wb');
        $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/text-files/app-routing.txt');
        if($f && $data){
            foreach($this->pages as $p){
                $data = str_replace(['APP_ROUTES','COMPONENT_IMPORT_STATEMENT'],
                    [$p->getRouteObj() . "\nAPP_ROUTES",$p->getImportStatement('.'.$this->getPath($this->pages,$p->id)) . "\nCOMPONENT_IMPORT_STATEMENT"], $data);
            }
            $data = str_replace(['APP_ROUTES','COMPONENT_IMPORT_STATEMENT'],
                ['', ''], $data);
            fwrite($f, $data);
        }
        if ($f) fclose($f);
        // services
        if(!file_exists($dir . '/services'))mkdir($dir . '/services');
        $f = fopen($dir . '/services' . '/trigger-service.ts', 'wb');// ik ga ervan uit dat dit sowieso nodig zal zijn omdat elke angular
        // applicatie wel ergens een click event heeft
        $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/text-files/service.txt');
        if($f && $data){
            $data = str_replace(['MODULE_IMPORT_STATEMENT','COMPONENT_IMPORT_STATEMENT','SERVICE_NAME','CONSTRUCTOR_INITIALIZATIONS','SERVICE_METHODS'],
                ['','','TriggerService','',''], $data);
            $vars='';
            $variables=[];
            $variableNames=[];
            foreach ($this->effects as $e){
                $variables[]=[lcfirst($e->trigger->name).ucfirst($e->source->name),$e->source->id];
                $variableNames[]=lcfirst($e->trigger->name).ucfirst($e->source->name);
            }
            $arr = array_count_values($variableNames);
            $keys = array_keys($arr);
            foreach ($keys as $key){
                if($arr[$key]>1){
                    foreach ($variables as $var){
                        if($var[0]===$key) $vars.=$key.'_'.$var[1]."=new EventEmitter();\n";
                    }
                } else{
                    $vars.=$key.'=new EventEmitter();';
                }
            }
            $data = str_replace(['SERVICE_VARIABLES'],
                [$vars], $data);
            $data = str_replace(['MODULE_IMPORT_STATEMENT','COMPONENT_IMPORT_STATEMENT','SERVICE_NAME','CONSTRUCTOR_INITIALIZATIONS','SERVICE_METHODS'],
                ['', '','','',''], $data);
            fwrite($f, $data);
        }
        if ($f) fclose($f);
        foreach ($this->pages as $p) {
            if ($this->isResourcePage($this->pages,$p)||$this->isMainPage($this->pages,$p)) {
                if(!file_exists($dir . $this->getPath($this->pages,$p->id)))mkdir($dir . $this->getPath($this->pages,$p->id));
                $f = fopen($dir . $this->getPath($this->pages,$p->id).'/' . $p->getPageFolderName() . '.component.html', 'wb');
                if($f){
                    $data = '';
                    foreach ($p->components as $c){
                        $data.=$c->getHTML()."\n";
                    }
                    fwrite($f, $data);
                    fclose($f);
                }
                $f = fopen($dir . $this->getPath($this->pages,$p->id).'/' . $p->getPageFolderName() . '.component.ts', 'wb');
                $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/text-files/resource-page.txt');
                if ($f && $data) {
                    $data = str_replace(['COMPONENT_CLASS_NAME','COMPONENT_SELECTOR','HTML_FILE_NAME','CSS_FILE_NAME'],
                        [$p->getPageComponentName(),$p->getHTMLSelector(),$p->getHTMLFilePath(),$p->getCSSFilePath()], $data);
                    $lon = $this->getLevelOfNesting($p);
                    foreach ($p->components as $c) {
                        $importStatements = strlen($c->getComponentImportStatements($lon,$this->pages))===0||!str_contains($data,$c->getComponentImportStatements($lon,$this->pages));
                            $cstr = $c->getConstructor();
                            $vars= $c->getVariables();
                            // todo  fix constructor werkt niet bij tweede component
                            if (is_array($cstr)){
                                if(is_array($vars)){
                                    $data = str_replace([
                                        'MODULE_IMPORT_STATEMENT',
                                        'COMPONENT_IMPORT_STATEMENT',
                                        'COMPONENT_VARIABLES',
                                        'NG_ON_INIT_BODY',
                                        'COMPONENT_CONSTRUCTOR',
                                        'COMPONENT_METHODS'],
                                        ["MODULE_IMPORT_STATEMENT",
                                          $importStatements ?  $c->getComponentImportStatements($lon,$this->pages)
                                            .implode("\n",$cstr[1])
                                            .implode("\n",$vars[1])
                                            . "\nCOMPONENT_IMPORT_STATEMENT":implode("\n",$cstr[1])
                                              .implode("\n",$vars[1])
                                              . "\nCOMPONENT_IMPORT_STATEMENT",
                                            $vars[0]. "\nCOMPONENT_VARIABLES",$c->getInit($this->pages) . "\nNG_ON_INIT_BODY",
                                            // todo verschillende constrcutor samenvoegen!
                                            $cstr[0]. "\nCOMPONENT_CONSTRUCTOR",//todo comp methods
                                            $c->getMethods()."\nCOMPONENT_METHODS"], $data);
                                } else{
                                    $data = str_replace([
                                        'MODULE_IMPORT_STATEMENT',
                                        'COMPONENT_IMPORT_STATEMENT',
                                        'COMPONENT_VARIABLES',
                                        'NG_ON_INIT_BODY',
                                        'COMPONENT_CONSTRUCTOR',
                                        'COMPONENT_METHODS'],
                                        ["MODULE_IMPORT_STATEMENT",
                                           $importStatements ? $c->getComponentImportStatements($lon,$this->pages)
                                            .implode("\n",$cstr[1])
                                            . "\nCOMPONENT_IMPORT_STATEMENT":implode("\n",$cstr[1])
                                               . "\nCOMPONENT_IMPORT_STATEMENT",
                                            $vars. "\nCOMPONENT_VARIABLES",$c->getInit($this->pages). "\nNG_ON_INIT_BODY",
                                            // todo verschillende constrcutor samenvoegen!
                                            $cstr[0]. "\nCOMPONENT_CONSTRUCTOR",//todo comp methods
                                            $c->getMethods()."\nCOMPONENT_METHODS"], $data);
                                }
                            } else{
                                if(is_array($vars)){
                                    $data = str_replace([
                                        'MODULE_IMPORT_STATEMENT',
                                        'COMPONENT_IMPORT_STATEMENT',
                                        'COMPONENT_VARIABLES',
                                        'NG_ON_INIT_BODY',
                                        'COMPONENT_CONSTRUCTOR',
                                        'COMPONENT_METHODS'],
                                        ["MODULE_IMPORT_STATEMENT",
                                          $importStatements ?  $c->getComponentImportStatements($lon,$this->pages)
                                            .implode("\n",$vars[1])
                                            . "\nCOMPONENT_IMPORT_STATEMENT":implode("\n",$vars[1])
                                              . "\nCOMPONENT_IMPORT_STATEMENT",
                                            $vars[0]. "\nCOMPONENT_VARIABLES",$c->getInit($this->pages). "\nNG_ON_INIT_BODY",
                                            // todo verschillende constrcutor samenvoegen!
                                            $cstr. "\nCOMPONENT_CONSTRUCTOR",//todo comp methods
                                            $c->getMethods()."\nCOMPONENT_METHODS"], $data);
                                } else{
                                    $data = str_replace([
                                        'MODULE_IMPORT_STATEMENT',
                                        'COMPONENT_IMPORT_STATEMENT',
                                        'COMPONENT_VARIABLES',
                                        'NG_ON_INIT_BODY',
                                        'COMPONENT_CONSTRUCTOR',
                                        'COMPONENT_METHODS',//todo comp methods
                                        ],
                                        ["MODULE_IMPORT_STATEMENT",
                                          $importStatements ?  $c->getComponentImportStatements($lon,$this->pages)
                                            . "\nCOMPONENT_IMPORT_STATEMENT":"COMPONENT_IMPORT_STATEMENT",
                                            $vars. "\nCOMPONENT_VARIABLES",$c->getInit($this->pages). "\nNG_ON_INIT_BODY",
                                            // todo verschillende constrcutor samenvoegen!
                                            $cstr. "\nCOMPONENT_CONSTRUCTOR",$c->getMethods()."\nCOMPONENT_METHODS"], $data);
                                }
                            }
                    }
                    $data = str_replace(['MODULE_IMPORT_STATEMENT', 'COMPONENT_IMPORT_STATEMENT','COMPONENT_CLASS_NAME',
                        'COMPONENT_SELECTOR',
                        'HTML_FILE_NAME',
                        'CSS_FILE_NAME',
                        'COMPONENT_VARIABLES',
                        'COMPONENT_CONSTRUCTOR',
                        'NG_ON_INIT_BODY',
                        'COMPONENT_METHODS'],
                        ['', '', '', '', '', '', '', '','',''], $data);
                    fwrite($f, $data);
                }
                if ($f) fclose($f);
                touch($dir . $this->getPath($this->pages,$p->id) . '/'.$p->getPageFolderName() . '.component.css');
            } else if ($this->isSubPage($this->pages,$p)) {
                // todo
            }
        }

    }
}