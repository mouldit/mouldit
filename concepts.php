<?php
function splitSchema($schemaContent): array
{
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
function getConceptData($codeBlock): array
{
    $name = trim(substr(strstr($codeBlock,'{',true),strpos($codeBlock,'type')+4));
    $fields = [];
    $block = trim(substr($codeBlock,strpos($codeBlock,'{')+1,strpos($codeBlock,'}')-strpos($codeBlock,'{')));
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
    return [$name,$fields];
}
function getConcepts($schema): array
{
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
        $concepts[]=new Concept($data[0],'abs',$data[1]);
    }
    foreach ($extBlocksAsStr as $ac){
        $data = getConceptData($ac);
        $concepts[]=new Concept($data[0],'ext',$data[1]);
    }
    foreach ($regBlocksAsStr as $ac){
        $data = getConceptData($ac);
        $concepts[]=new Concept($data[0],'reg',$data[1]);
    }
    return $concepts;
}