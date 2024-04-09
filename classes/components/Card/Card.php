<?php

namespace components\Card;
use components\groups\ContentInjection;
use components\groups\Dimensioning;
use components\groups\Visibility;
use components\IComponent;
use Exception;

class Card extends \components\Component  implements IComponent
{
    // todo: zet nu de contentInjection in de Component Config GUI
    public string $header;
    public string $subheader;
    public ContentInjection $ci;
    use Visibility;
    use Dimensioning;
    /**
     * @throws Exception
     */
    public function __construct($id, $pageId, $name, $type, $header = NULL, $subheader=NULL){
        //      technisch kan je nagaan of er maar één type is ContentInjection , het wijzigen van de waarden kan door alles en iedereen,
        //      wat niet per se fout is, maar je kan het wel maar door één methode nu, namelijk de changeContentInjection method in de klasse
        //      van het type zelf, dus de waardes kunnen enkel gewijzigd worden op een correcte manier, d.w.z. o.a. overeenkomstig
        //      de definitie zoals bepaald in de constructor
        parent::__construct($id,$pageId,$name, $type);
        if(isset($header)) $this->header=$header;
        if(isset($subheader)) $this->subheader=$subheader;
        $this->ci=new ContentInjection('content','header','footer');
    }
    public function getAttributes(){
        return ['header','subheader'];
    }
    // todo sommige componenten maken gebruik van pagina componenten die dan ook geïmporteerd worden
    function getImportStatement()
    {
        // todo de laatste drie horen hier niet thuis!
        return ['import {CardModule} from "primeng/card";'];
    }
    function getControllerImports(){
        return [
            'import { HttpClient } from \'@angular/common/http\';',
            'import { Observable, throwError } from \'rxjs\';',
            'import { catchError, map } from \'rxjs/operators\';'];
    }

    function getImportsStatement()
    {
        return "\n".'CardModule,';
    }

    function getControllerVariables()
    {
        return '';
    }

    function getInit($pages)
    {
        return '';
    }

    function getConstructorInjections()
    {
        // todo de overeenkomstige import gebeurt nog niet
        return 'private http: HttpClient,';
    }

    function getHTML(string $triggers,\Action $action=null)
    {
        if(isset($action) && $action->getReturnType()==='list'){
            $html =  '<ng-container *ngFor="let '.$action->concept.' of '.$action->concept.'s'.'; let i = index">
            <p-card '.$triggers.' ';
            if(isset($this->mapping['header'])){
               $html.='header="{{'.$action->concept.'.'.$this->mapping['header'].'}}" ';
            }
            if(isset($this->mapping['subheader'])){
                $html.='subheader="{{'.$action->concept.'.'.$this->mapping['subheader'].'}}" ';
            }
            $html.='></p-card></ng-container>';
            return $html;
        } else{
            return '';
        }
    }

}
