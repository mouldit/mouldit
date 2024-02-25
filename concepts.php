<?php
function splitSchema($schemaContent){
    $filtered = $schemaContent;
    $abstractBlocks = [];
    while($nextAbstract = strpos($filtered,'abstract')){
        $nextCodeBlock = strpos($filtered,'{',$nextAbstract);
        $keepFirst = strstr($filtered,'abstract',true);
        $endCodeBlock = strpos($filtered,'}',$nextCodeBlock);
        $next = strpos($filtered,'{',$nextCodeBlock);
        while($endCodeBlock>$next){
            $next=strpos($filtered,'{',$endCodeBlock);
            $endCodeBlock = strpos($filtered,'}',$endCodeBlock);
        }
        $abstractBlocks[]=trim(substr($filtered,$nextAbstract,$endCodeBlock-$nextAbstract));
        $keepSecond = substr($filtered,$endCodeBlock+1);
        $filtered=$keepFirst.$keepSecond;
    }
    return [$filtered,$abstractBlocks];
}
function getConcepts($schema){
    $concepts = [];
    $first = strpos($schema,'{')+1;
    $last = strrpos($schema,'}')-1;
    $schemaContent = trim(substr($schema,$first,$last-$first));
    $temp = splitSchema($schemaContent);
    $codeBlocks = $temp[0];
    $abstractCodeBlocks = $temp[1];
    // abstracte concepten eerst
    foreach ($abstractCodeBlocks as $ac){
        // todo strategie:
        $next = getAbstractConceptCodeBlock($ac);
        if($next){
            $concepts[]=processAbstractConcept($next); // toevoegen velden aan concept + aanmaken concept
        }
    }
    $next = strstr($schemaContent, 'abstract');
    if($next){
        $next = getAbstractConceptCodeBlock($next);
        if($next){
            $concepts[]=processAbstractConcept($next);
        }
        $expl = explode($next,$schemaContent);
        while (sizeof($expl) > 1 && $next = strstr($expl[1], 'abstract')) {
            $next = getAbstractConceptCodeBlock($next);
            if($next)processAbstractConcept($next);
            $expl = explode($next,$schemaContent);
        }
    }
    // nu de andere concepten
    // strategie: eerst de abstracte blokken eruit filteren
    $filteredSchemaContent = clearAbstractBlocks($schemaContent);
    $arr = explode('type', $schemaContent);
    $arr = array_slice($arr, 1);
    $arrFiltered = [];
    for ($i = 0; $i < sizeof($arr); $i++){
        $found = false;
        for ($j = 0; $j < sizeof($_SESSION['actions']); $j++){
            if($_SESSION['actions'][$j]->name==='Get all '.trim(substr($arr[$i],0,strpos($arr[$i],'{'))).'s'){
                $found = true;
                break;
            }
        }
        if(!$found)$arrFiltered[]=$arr[$i];
    }
    for ($i = 0; $i < sizeof($arrFiltered); $i++) {
        $concept = $arrFiltered[$i];
        $fields=null;
        // todo ook dit hier is eigenlijk pure concept info
        if (str_contains($concept, 'extending')) {
            $abstract = trim(strstr($concept, 'extending'));
            $end = strpos($abstract,'{');
            $start = strlen('extending');
            $abstract = trim(substr($abstract,$start,$end-$start));
            $concept = trim(strstr($concept, 'extending', true));
            for ($j=0;$j<sizeof($_SESSION['actions']);$j++){
                if($_SESSION['actions'][$j]->name==='Get all '.$abstract.'s'){
                    $fields = $_SESSION['actions'][$j]->fields;
                }
            }
        } else {
            $concept = trim(explode('{', $concept)[0]);
        }
        $_SESSION['concepts'][]=$concept;
        $action = new Action('Get all ' . $concept . 's',$implementedTypesOfActions[0][1],$implementedTypesOfActions[0][0]);
        if($fields)array_push($action->fields,...$fields);
        // todo add subfields too
        addFields($action,$arrFiltered[$i]);
        if ($i === 0) {
            $action->selected = true;
        }
        $_SESSION['actions'][] = $action;
    }
}
function getAbstractConceptCodeBlock($next): string {
    $next = trim(substr($next, strlen('abstract type')));
    // dit geeft alle code vanaf de naam van het eerste abstracte concept
    $posType = strpos($next, 'type');
    $posAbstractType = strpos($next, 'abstract type'); // in het voorbeeld is dit getal groter
    // de redenering is dat je de code neemt tot het VOLGENDE concept waarbij je checkt van welk type dat is
    if(!$posType&&!$posAbstractType){
        $next = trim(substr($next,0,strrpos($next,'}')));
    } else if(($posType && !$posAbstractType)||($posType&&$posAbstractType&&$posType<$posAbstractType)){
        $next = trim(substr($next, 0, $posType));
    } else if((!$posType && $posAbstractType)||($posType&&$posAbstractType&&$posType>$posAbstractType)){
        $next = trim(substr($next, 0, $posAbstractType));
    }
    return $next;
}
function addFields(Concept &$concept, string $next){
    // hier wordt de text gehaald van de concept body
    $start = strpos($next, '{') + 1;
    $end = strrpos($next, '}');
    $conceptBlock = substr($next, $start, $end);
    while (str_contains($conceptBlock, '{')) {
        // hier worden bijkomende constraint er tussenuit gehaald
        $first = trim(strstr($conceptBlock, '{', true));
        $last = substr($conceptBlock, strpos($conceptBlock, '}') + 1);
        $conceptBlock = $first . $last;
    }
    // todo extraheer dit om $concepts mee op te bouwen
    //      $concepts gebruik je vervolgens om $actions op te bouwen
    //      aangezien dit niets anders is dan er een verb aan te koppelen met wat extra metadata
    $propChunks = explode(';', $conceptBlock);
    array_pop($propChunks);
    foreach ($propChunks as $chunk) {
        $chunk = trim($chunk);
        $parts = explode(':',$chunk);
        $parts[0]=trim($parts[0]);
        $parts[1]=trim($parts[1]);
        $type = str_replace(' ','',$parts[1]);
        $fieldExpl = explode(' ',$parts[0]);
        if(is_array($fieldExpl)){
            $fieldName = array_pop($fieldExpl);
            $field = new Field($fieldName,$type,true);
            $concept->addField($field);
        }
    }
}

function processAbstractConcept($next): Concept{
    $conceptName = trim(strstr($next, '{', true));
    $concept = new Concept($conceptName,'abstract');
    addFields($concept, $next);
    return $concept;
}