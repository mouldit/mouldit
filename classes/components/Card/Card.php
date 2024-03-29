<?php

namespace components\Card;

use IComponent;

class Card extends \Component  implements IComponent
{
    public string $header;
    public string $subheader;

    public function __construct($id,$pageId,$name, $type,$header = NULL, $subheader=NULL)
    {
        parent::__construct($id,$pageId,$name, $type);
        if(isset($header)) $this->header=$header;
        if(isset($subheader)) $this->subheader=$subheader;
    }

    public function getAttributes()
    {
        return ['header','subheader'];
    }
    // todo sommige componenten maken gebruik van pagina componenten die dan ook geïmporteerd worden
    function getImportStatement()
    {
        return "\n".'import {CardModule} from "primeng/card";';
    }

    function getImportsStatement()
    {
        return "\n".'CardModule,';
    }

    function getComponentImportStatements( int $levelsOfNesting,array $pages)
    {
        return '';
    }

    function getVariables()
    {
        if(isset($this->actionLink)){
            return $this->actionLink->concept.'s:any=undefined;';
        }
        return "";
    }

    function getInit($pages)
    {
        // todo implement activated route on menubar
        if(isset($this->actionLink)){
            return 'this.http.'.$this->actionLink->verb.'(\'http://localhost:5000/'
                .$this->actionLink->concept.'/'.$this->actionLink->concept.'s\').subscribe(res => {
            this.'.$this->actionLink->concept.'s=res;
        });';
        }
        return '';
    }

    function getConstructor()
    {
       return ['constructor(private http: HttpClient) {}',
           [ 'import { HttpClient } from \'@angular/common/http\';'."\n",
        'import { Observable, throwError } from \'rxjs\';'."\n",
        'import { catchError, map } from \'rxjs/operators\';'."\n"]
       ];
    }

    function getHTML()
    {
        if(isset($this->actionLink) && $this->actionLink->getReturnType()==='list'){
            return '<ng-container *ngFor="let '.$this->actionLink->concept.' of '.$this->actionLink->concept.'s'.'; let i = index">
            <p-card 
            header="{{'.$this->actionLink->concept.'.'.$this->mapping['header'].'}}" 
            subheader="{{'.$this->actionLink->concept.'.'.$this->mapping['subheader'].'}}"></p-card>
          </ng-container>';
        } else{
            return '';
        }
    }
}