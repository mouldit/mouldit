<?php
function splitSchema($schemaContent){
    $abstractBlocks = [];
    $regBlocks = [];
    $extendBlocks = [];
    $schemaContent = trim($schemaContent);
    while($schemaContent){
        $nextClosingTag = strpos($schemaContent,'}');
        $nextOpeningTag =strpos($schemaContent,'{',strpos($schemaContent,'{')+1);
        while($nextClosingTag>$nextOpeningTag){
            $nextClosingTag = strpos($schemaContent,'}',$nextClosingTag+1);
            $nextOpeningTag = strpos($schemaContent,'{',$nextOpeningTag+1);
        }
        $codeBlock = substr($schemaContent,0,$nextClosingTag);
        if(str_contains($codeBlock,'extending')){
            $extendBlocks[]=$codeBlock;
        } else if(str_contains($codeBlock,'abstract')){
            $abstractBlocks[]=$codeBlock;
        } else{
            $regBlocks[]=$codeBlock;
        }
        $schemaContent = substr($schemaContent,strlen($codeBlock));
    }
    return [$regBlocks,$extendBlocks,$abstractBlocks];
}
function getConcepts($schema){
    $concepts = [];
    $first = strpos($schema,'{')+1;
    $last = strrpos($schema,'}')-1;
    $schemaContent = trim(substr($schema,$first,$last-$first));
    $temp = splitSchema($schemaContent);
    $codeBlocksAsStr = $temp[0];
    $abstractCodeBlocks = $temp[1];
    // abstracte concepten eerst
    foreach ($abstractCodeBlocks as $ac){
        $name = trim(substr(strstr($ac,'{',true),strpos($ac,'type')+4));
        $fields = [];
        $block = trim(substr($ac,strpos($ac,'{')+1,strpos($ac,'}')-strpos($ac,'{')));
        $props = explode(';',$block);
        array_pop($props);
        foreach ($props as $prop){
            $t=explode(':',trim($prop));
            $f = explode(' ',$t[0]);
            if(is_array($f)){
                $fieldName = trim(array_pop($f));
                $fieldType = trim($t[1]);
                $fields[]=new Field($fieldName,$fieldType);
            }
        }
        $concepts[]=new Concept($name,'abstract',$fields);
    }
    // nu de andere concepten


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