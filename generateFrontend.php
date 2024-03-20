<?php
function generateFrontend($dir,$pages){
    // todo welke data heeft deze functie nodig?
/*    chdir($dir); // getest = ok
    exec('npx ng g c main-page');*/
    for ($i=0;$i<sizeof($pages);$i++){
        if($pages[$i]->main){
            printMainPage($pages[$i],$dir);
            break;
        }
    }
    $f = fopen($dir.'/app.component.html','wb');
    $data = "<app-main-page></app-main-page>"."\n"."<router-outlet />";
    fwrite($f,$data);
    fclose($f);
    $f = fopen($dir.'/app.component.ts','wb');
    $data="import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import {MainPageComponent} from \"./main-page/main-page.component\";

@Component({
  selector: 'app-root',
  standalone:true,
  imports: [RouterOutlet, MainPageComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'client';
}";
    fwrite($f,$data);
    fclose($f);
}

function printPage($p){

}
function printSubPage($sp){

}
function printMainPage($mp,$dir){
    mkdir($dir.'/main-page');
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
        $oninit = '';
        foreach ($mp->components as $c){
            switch ($c->type){
                case 'menubar':
                    // todo add items to oninit
                    $vars.='items: MenuItem[] | undefined;'."\n";
                    $imports.='import { MenuItem } from \'primeng/api\';'."\n";
                    $oninit.="\n".'this.items=['."\n";
                    foreach ($c->menuItems as $menuItem){
                        $oninit.="{\t".'label:\''.$menuItem->name.'\'},'."\n";
                    }
                    $oninit.=']'."\n";
                    break;
                case 'card':
                    break;
                case 'table':
                    break;
            }
        }
        $data = str_replace(['IMPORTS','VARS','ONINIT'],[$imports,$vars,$oninit],$data);
        fwrite($f,$data);
        fclose($f);
    }
}