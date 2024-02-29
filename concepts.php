<?php
function getConcepts($schema): array {
    $concepts = [];
    $first = strpos($schema,'{')+1;
    $last = strrpos($schema,'}')-1;
    $schemaContent = trim(substr($schema,$first,$last-$first));
    $temp = splitSchema($schemaContent);
    $regBlocksAsStr = $temp[0];
    $extBlocksAsStr = $temp[1];
    $abstractCodeBlocks = $temp[2];
    foreach ($abstractCodeBlocks as $ac){
        $data = getConceptData($ac);
        $concept = new Concept($data[0],'abs');
        $concept->addFields($data[1]);
        $concepts[] = $concept;
    }
    foreach ($extBlocksAsStr as $ac){
        $data = getConceptData($ac);
        $from = strpos($data[0],'extending')+strlen('extending');
        $extendsFrom = trim(substr($data[0],$from));
        for ($i=0;$i<sizeof($concepts);$i++){
            if($concepts[$i]->name===$extendsFrom){
                $name = trim(strstr($data[0],'extending',true));
                $concept =  new Concept($name,'ext');
                $concept->setFields(clone $concepts[$i]->fields);
                $concept->addFields($data[1]);
                $concepts[] = $concept;
                break;
            }
        }
    }
    foreach ($regBlocksAsStr as $ac){
        $data = getConceptData($ac);
        $concept = new Concept($data[0],'reg');
        $concept->addFields($data[1]);
        $concepts[] = $concept;
    }
    return $concepts;
}
function splitSchema($schemaContent): array
{
    $abstractBlocks = [];
    $regBlocks = [];
    $extendBlocks = [];
    $schemaContent = trim($schemaContent);
    while($schemaContent){
        $nextClosingTag = strpos($schemaContent,'}');
        $offset = strpos($schemaContent,'{');
        $nextOpeningTag =strpos($schemaContent,'{',$offset+1);
        // todo de tussenin properties moeten er wel uit en dat gebeurt niet!
        while($nextOpeningTag!==false&&$nextClosingTag>$nextOpeningTag){
            $nextClosingTag = strpos($schemaContent,'}',$nextClosingTag+1);
            $nextOpeningTag = strpos($schemaContent,'{',$nextOpeningTag+1);
        }
        $codeBlock = substr($schemaContent,0,$nextClosingTag+1);
        echo htmlspecialchars($codeBlock);
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
function getConceptData($codeBlock): array
{
    $name = trim(substr(strstr($codeBlock,'{',true),strpos($codeBlock,'type')+4));
    $fields = [];
    $block = trim(substr($codeBlock,strpos($codeBlock,'{')+1,strpos($codeBlock,'}')-strpos($codeBlock,'{')));
    $props = explode(';',$block);
    array_pop($props);
    //echo '<pre>props as strings => '.print_r($props, true).'</pre><br>';
    foreach ($props as $prop){
        $t=explode(':',trim($prop));
        $f = explode(' ',trim($t[0]));
        if(is_array($f)){
            $fieldName = trim(array_pop($f));
            $fieldType = trim($t[1]);
            $fields[]=new Field($fieldName,$fieldType);
        }
    }
    return [$name,$fields];
}
