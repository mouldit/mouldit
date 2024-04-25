<?php

class Frontend
{
    use FrontendMethods;
    public array $pages = [];
    public array $effects = [];
    public function removeEffect(int $id){
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
    public function generate(string $dir): void{
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
            // todo we krijgen een dubbele import als de import van een module op twee niveaus bestaat:
            //      een top en een nested level
            $data = str_replace(['COMPONENT_IMPORT_STATEMENT','MODULE_IMPORTS_STATEMENT', 'MODULE_PROVIDER_STATEMENT'],
                ["\nimport { HttpClientModule } from '@angular/common/http';
                \nimport {TriggerService} from \"./services/trigger-service\";\nCOMPONENT_IMPORT_STATEMENT",
                    "\nHttpClientModule,\nMODULE_IMPORTS_STATEMENT","TriggerService\n"], $data);
            $import = [];
            $imports=[];
            foreach ($this->pages as $p) {
                // todo test dit om te zien over er over pagina's heen geen dubbeleme imports en zo gebeuren
                $data = str_replace(['COMPONENT_IMPORT_STATEMENT', 'COMPONENT_DECLARATIONS_STATEMENT','MODULE_PROVIDER_STATEMENT'],
                    [$p->getImportStatement('.'.$this->getPath($this->pages,$p->id)) . "\nCOMPONENT_IMPORT_STATEMENT",
                       $p->getDeclarationsStatement() . "\nCOMPONENT_DECLARATIONS_STATEMENT",""], $data);
                foreach ($p->components as $c) {
                    // todo replace with merge and unique array
                    if (!in_array($c->type, $declared)) {
                        $declared[] = $c->type;
                        $import = array_merge($import,$c->getImportStatement());
                        $imports = array_merge($imports,$c->getImportsStatement());
/*                        $data = str_replace([ 'MODULE_IMPORTS_STATEMENT'],
                            [$c->getImportsStatement()
                                . "\nMODULE_IMPORTS_STATEMENT"], $data);*/
                        if(isset($c->ci->contentInjection)){
                            $comps = array_values($c->ci->contentInjection);
                            foreach ($comps as $comp){
                                if(isset($comp)){
                                    // todo maak hier een volwaardige nesting van met een while loop
                                    $declared[] = $comp->type;
                                    $temp = $comp->getImportStatement();
                                    $merged = array_merge($import, $temp);
                                    $import = array_unique($merged);
                                    $imports = array_unique(array_merge($imports,$comp->getImportsStatement()));
/*                                    $data = str_replace(['MODULE_IMPORTS_STATEMENT'],
                                        [$comp->getImportsStatement()
                                            . "\nMODULE_IMPORTS_STATEMENT"], $data);*/
                                }
                            }
                        }
                        //echo '<pre> import  = '.print_r($import, true).'</pre>';
                    }
                }
            }
            $data = str_replace(['MODULE_IMPORT_STATEMENT','MODULE_IMPORTS_STATEMENT'],
                [join("\n",$import) . "\nMODULE_IMPORT_STATEMENT",
                    join(",\n",$imports) . "\nMODULE_IMPORTS_STATEMENT"
                ], $data);
            $data = str_replace(['MODULE_IMPORT_STATEMENT', 'MODULE_IMPORTS_STATEMENT', 'COMPONENT_IMPORT_STATEMENT', 'COMPONENT_DECLARATIONS_STATEMENT',
                'MODULE_PROVIDER_STATEMENT'],
                ['', '', '', '',''], $data);
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
                // todo het kan nuttig zijn op termijn om target s toch te laten inschrijven op on page load events van andere componenten?
                if(!($e->trigger instanceof \Enums\PageTriggerType)){
                    $variables[]=[lcfirst($e->trigger->name).ucfirst($e->source->name),$e->source->id];
                    $variableNames[]=lcfirst($e->trigger->name).ucfirst($e->source->name);
                }
            }
            $arr = array_count_values($variableNames);
            $keys = array_keys($arr);
            foreach ($keys as $key){
                if($arr[$key]>1){
                    foreach ($variables as $var){
                        if($var[0]===$key) $vars.=$key.'_'.$var[1]."=new EventEmitter();\n";
                    }
                } else{
                    $vars.=$key.'=new EventEmitter();'."\n";
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
                // HTML bestand = ComponentView
                if(!file_exists($dir . $this->getPath($this->pages,$p->id)))mkdir($dir . $this->getPath($this->pages,$p->id));
                $f = fopen($dir . $this->getPath($this->pages,$p->id).'/' . $p->getPageFolderName() . '.component.html', 'wb');
                if($f){
                    $data = '';
                    foreach ($p->components as $c){
                        $triggers = '';
                        $action = null;
                        $ciComps = [];
                        $nested = $p->getNestedComponents($c->id);
                        foreach ($this->effects as $e){
                            if($e->source->id===$c->id){
                                $triggers.="\n{$e->getTrigger()}";
                            }
                            if($e->target->id===$c->id){
                                $action = $e->action;
                            }
                        }
                        for ($i=0;$i<sizeof($nested);$i++){
                            $ciEl = $nested[$i]->id;
                            $ciElTriggers = '';
                            $ciElAction = NULL;
                            foreach ($this->effects as $e){
                                if($e->source->id===$nested[$i]->id){
                                    $ciElTriggers.="\n{$e->getTrigger()}";
                                }
                                if($e->target->id===$c->id){
                                    $ciElAction = $e->action;
                                }
                            }
                            $ciComps[]=[$ciEl,$ciElTriggers,$ciElAction];
                        }
                        $data.=$c->getHTML($triggers,$action,$ciComps)."\n";
                        // todo container component voorzien voor als je meerdere componenten in een block wilt toevoegen
                    }
                    fwrite($f, $data);
                    fclose($f);
                }
                // TS bestand = ComponentController
                $f = fopen($dir . $this->getPath($this->pages,$p->id).'/' . $p->getPageFolderName() . '.component.ts', 'wb');
                $data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/text-files/resource-page.txt');
                if ($f && $data) {
                    $data = str_replace(['COMPONENT_CLASS_NAME','COMPONENT_SELECTOR','HTML_FILE_NAME','CSS_FILE_NAME'],
                        [$p->getPageComponentName(),$p->getHTMLSelector(),$p->getHTMLFilePath(),$p->getCSSFilePath()], $data);
                    $p->createViewController($data,$this->effects,$this->pages);
                    $data = str_replace([
                        'MODULE_IMPORT_STATEMENT',
                        'COMPONENT_IMPORT_STATEMENT',
                        'COMPONENT_CLASS_NAME',
                        'COMPONENT_SELECTOR',
                        'HTML_FILE_NAME',
                        'CSS_FILE_NAME',
                        'COMPONENT_VARIABLES',
                        'CONSTRUCTOR_VARIABLES',
                        'NG_ON_INIT_BODY',
                        'COMPONENT_METHODS'],
                        ['', '', '', '', '', '', '', '','',''], $data);
                    fwrite($f, $data);
                }
                if ($f) fclose($f);
                // Component Style bestand
                touch($dir . $this->getPath($this->pages,$p->id) . '/'.$p->getPageFolderName() . '.component.css');
            } else if ($this->isSubPage($this->pages,$p)) {
                // todo
            }
        }
    }
}
