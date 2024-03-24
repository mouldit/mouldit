<?php
// todo create model files? voorlopig gewoon array type
function generateFrontend($dir,$pages){
/*    chdir($dir); // getest = ok
    exec('npx ng g c main-page');*/
    for ($i=0;$i<sizeof($pages);$i++){
        if($pages[$i]->main){
            printMainPage($pages[$i],$dir,$pages);
        } else{
            printPage($pages[$i],$dir,$pages);
        }
    }
    // app landingspage
    $f = fopen($dir.'/app.component.html','wb');
    $data = "<app-main-page></app-main-page>"."\n"."<router-outlet />";
    fwrite($f,$data);
    fclose($f);
    $f = fopen($dir.'/app.component.ts','wb');
    $data="import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import {MainPage} from \"./main-page/main-page.component\";

@Component({
  selector: 'app-root',
  standalone:true,
  imports: [RouterOutlet, MainPage],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'client';
}";
    fwrite($f,$data);
    fclose($f);
}

function printPage($p,$dir, $pages){
    if(isset($p->parentId)){
        printSubPage($p,$dir,$pages);
    } else{
        $dirName = $dir.'/'.getPageFolderName($p->name);
        if(!file_exists($dirName))mkdir($dirName);
        touch($dirName.'/'.getPageFolderName($p->name).'.component.css');
        $f = fopen($dirName.'/'.getPageFolderName($p->name).'.component.html','wb');
        if($f){
            $data = '';
            foreach ($p->components as $c){
                switch ($c->type){
                    case 'menubar':
                        $data.="<p-menubar [model]=\"items\"></p-menubar>"."\n";
                        break;
                    case 'card':
                        if(isset($c->actionLink) && $c->actionLink->getReturnType()==='list'){
                            echo '<pre>'.print_r($c->mapping, true).'</pre>';
                            $data.='<ng-container *ngFor="let '.$c->actionLink->concept.' of '.$c->actionLink->concept.'s'.'; let i = index">
            <p-card 
            header="'.$c->actionLink->concept.'.'.$c->mapping['header'].'" 
            subheader="'.$c->actionLink->concept.'.'.$c->mapping['subheader'].'"></p-card>
          </ng-container>';
                        } else{

                        }
                        break;
                    case 'table':
                        break;
                }
            }
            fwrite($f,$data);
            fclose($f);
        }
        $f = fopen($dirName.'/'.getPageFolderName($p->name).'.component.ts','wb');
        if($f){
            $data = file_get_contents('resource-page.txt');
            $data = str_replace(['RESOURCE','COMPNAME'],[getPageFolderName($p->name),getPageComponentName($p->name)],$data);
            $vars='';
            $imports='';
            $compImports = '';
            $oninit = '';
            $constructor='';
            foreach ($p->components as $c){
                switch ($c->type){
                    case 'menubar':
                        $vars.='items: MenuItem[] | undefined;'."\n";
                        $imports.='import { MenuItem } from \'primeng/api\';'."\n";
                        $imports.='import { MenubarModule } from \'primeng/menubar\';'."\n";
                        $compImports.='MenubarModule, ';
                        $oninit.="\n".'this.items=['."\n";
                        foreach ($c->menuItems as $menuItem){
                            if($menuItem->page){
                                for ($i=0;$i<sizeof($pages);$i++){
                                    if($pages[$i]->id===$menuItem->page){
                                        $compName = getPageComponentName($pages[$i]->name);
                                        $oninit.="{\t".'label:\''.$menuItem->name.'\', routerLink:'.$compName.'},'."\n";
                                        $imports.='import { '.$compName.' } from \''.getPath($pages[$i],$p,$pages).'\';'."\n";
                                        break;
                                    }
                                }
                            } else{
                                $oninit.="{\t".'label:\''.$menuItem->name.'\'},'."\n";
                            }
                        }
                        $oninit.=']'."\n";
                        break;
                    case 'card':
                        // todo voorlopig hardcoded meervoud van concept bij actie
                        // todo voeg modellen toe zodat je dit naderhand kan typescripten
                        $vars = $c->actionLink->concept.'s:any=undefined;';
                        $constructor.='constructor(private http: HttpClient) {}';
                        $imports.='import { CardModule } from \'primeng/card\';'."\n";
                        $imports.='import { HttpClient } from \'@angular/common/http\';'."\n";
                        $imports.='import { Observable, throwError } from \'rxjs\';'."\n";
                        $imports.='import { catchError, map } from \'rxjs/operators\';'."\n";
                        $compImports.='CardModule, ';
                        // todo url er dynamisch uithalen is nu hardcoded
                        $oninit.='this.http.'.$c->actionLink->verb.'(\'http://localhost:5000/'.$c->actionLink->concept.'s\').pipe(map((err, res) => {
            this.'.$c->actionLink->concept.'s=res;
        }));';
                        break;
                    case 'table':
                        break;
                }
            }
            $data = str_replace(['IMPORTS','VARS','ONINIT','COMPMPRTS','CONSTRUCTOR'],[$imports,$vars,$oninit,$compImports,$constructor],$data);
            fwrite($f,$data);
            fclose($f);
        }
    }
}
function printSubPage($sp,$dir, $pages){

}
function printMainPage($mp,$dir, $pages){
    if(!file_exists($dir.'/main-page'))mkdir($dir.'/main-page');
    touch($dir.'/main-page/main-page.component.css');
    $f = fopen($dir.'/main-page/main-page.component.html','wb');
    if($f){
        $data = '';
        foreach ($mp->components as $c){
            switch ($c->type){
                case 'menubar':
                    $data.="<p-menubar [model]=\"items\"></p-menubar>"."\n";
                    break;
                case 'card':
                    break;
                case 'table':
                    break;
            }
        }
        fwrite($f,$data);
        fclose($f);
    }
    $f = fopen($dir.'/main-page/main-page.component.ts','wb');
    if($f){
        $data = file_get_contents('resource-page.txt');
        $data = str_replace(['RESOURCE','COMPNAME'],['main-page','MainPage'],$data);
        $vars='';
        $imports='';
        $compImports = '';
        $oninit = '';
        $constructor='';
        foreach ($mp->components as $c){
            switch ($c->type){
                case 'menubar':
                    $vars.='items: MenuItem[] | undefined;'."\n";
                    $imports.='import { MenuItem } from \'primeng/api\';'."\n";
                    $imports.='import { MenubarModule } from \'primeng/menubar\';'."\n";
                    $compImports.='MenubarModule, ';
                    $oninit.="\n".'this.items=['."\n";
                    foreach ($c->menuItems as $menuItem){
                        if($menuItem->page){
                            for ($i=0;$i<sizeof($pages);$i++){
                                if($pages[$i]->id===$menuItem->page){
                                    $compName = getPageComponentName($pages[$i]->name);
                                    $oninit.="{\t".'label:\''.$menuItem->name.'\', routerLink:'.$compName.'},'."\n";
                                    // todo test import { ContentsPageComponent} from "../contents-page/contents-page.component";
                                    $imports.='import { '.$compName.' } from \''.getPath($pages[$i],$mp,$pages).'/'.getPageFolderName($pages[$i]->name).'.component\';'."\n";
                                    break;
                                }
                            }
                        } else{
                            $oninit.="{\t".'label:\''.$menuItem->name.'\'},'."\n";
                        }
                    }
                    $oninit.=']'."\n";
                    break;
                case 'card':
                    break;
                case 'table':
                    break;
            }
        }
        $data = str_replace(['IMPORTS','VARS','ONINIT','COMPMPRTS','CONSTRUCTOR'],[$imports,$vars,$oninit,$compImports,$constructor],$data);
        fwrite($f,$data);
        fclose($f);
    }
}

function getPageComponentName($pageName){
    $componentName = explode('_',$pageName);
    $componentName = array_slice($componentName,-2);
    array_walk($componentName,function (&$el,$index){
        $el = ucfirst($el);
    });
    return implode('',$componentName).'Component';
}
function getPageFolderName($pageName){
    $folderName = explode('_',$pageName);
    $folderName = array_slice($folderName,-2);
    return implode('-',$folderName);
}
function getFolderPath(Page $p,$pages){
    $path = getPageFolderName($p->name);
    $comp = $p->parentId ?? NULL;
    while($comp){
        for ($i=0;$i<sizeof($pages);$i++){
            if($pages[$i]->id===$comp){
                $comp = $pages[$i]->parentId ?? NULL;
                $path=getPageFolderName($pages[$i]->name).'/'.$path;
                break;
            }
        }
    }
    return $path;
}
function getPath(Page $target,Page $current,$pages){
    $levelDir = '../';
    $parent = $current->parentId ?? NULL;
    while($parent){
        $levelDir.='../';
        $parent=NULL;
        for ($i=0;$i<sizeof($pages);$i++){
            if($pages[$i]->id===$parent){
                $parent=$pages[$i]->parentId ?? NULL;
                break;
            }
        }
    }
    return $levelDir.getFolderPath($target,$pages);
}