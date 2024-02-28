<?php
function getConcepts($schema): array {
    // todo fix loopt serieus fout
    $concepts = [];
    $first = strpos($schema,'{')+1;
    $last = strrpos($schema,'}')-1;
    $schemaContent = trim(substr($schema,$first,$last-$first));
    $temp = splitSchema($schemaContent);
    $regBlocksAsStr = $temp[0];
    $extBlocksAsStr = $temp[1];
    $abstractCodeBlocks = $temp[2];
    // ok tot hier
    foreach ($abstractCodeBlocks as $ac){
        $data = getConceptData($ac);
        $concept = new Concept($data[0],'abs');
        $concept->addFields($data[1]);
        //echo '<pre>abs concept => '.print_r($concept, true).'</pre>';
        $concepts[]=$concept;
    }
    foreach ($extBlocksAsStr as $ac){
        // todo movies en shows worden niet gevonden
        $data = getConceptData($ac);
        $from = strpos($data[0],'extending')+strlen('extending');
        echo '<pre>concept data for  => '.print_r($data, true).'</pre>';
        $extendsFrom = trim(substr($data[0],$from));
        //echo 'in => '.$extendsFrom.' from: '.$from;
        for ($i=0;$i<sizeof($concepts);$i++){
            if($concepts[$i]->name===$extendsFrom){
                echo 'in';
                $concept = new Concept($data[0],'ext');
                $concept->setFields($concepts[$i]->fields);
                $concept->addFields($data[1]);
                $concepts[]=$concept;
                break;
            }
        }
    }
    foreach ($regBlocksAsStr as $ac){
        $data = getConceptData($ac);
        $concept = new Concept($data[0],'reg');
        $concept->addFields($data[1]);
        $concepts[]=$concept;
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
        while($nextOpeningTag!==false&&$nextClosingTag>$nextOpeningTag){
            $nextClosingTag = strpos($schemaContent,'}',$nextClosingTag+1);
            $nextOpeningTag = strpos($schemaContent,'{',$nextOpeningTag+1);
        }
        $codeBlock = substr($schemaContent,0,$nextClosingTag+1);
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
    if(str_contains($name,'extending')){
        // todo dit is niet goed want je moet weten waarvan het extends om de velden naderhand te kunnen toevoegen
        $name=trim(strstr($name,'extending',true));
        echo $name;
    }
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
