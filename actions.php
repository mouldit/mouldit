<?php
session_start();
include './Action.php';
global $implementedTypesOfActions;
if (isset($_SESSION['pathToRootOfServer']) &&
    $dir = opendir($_SESSION['pathToRootOfServer']) &&
    file_exists($_SESSION['pathToRootOfServer'].'/dbschema/default.esdl') &&
        !isset($_SESSION['actions'])) {
    $implementedTypesOfActions = [
        'GET ALL'
    ];
    $fileAsStr = file_get_contents($_SESSION['pathToRootOfServer'].'/dbschema/default.esdl');
    $arr = explode('type',$fileAsStr);
    $arr = array_slice($arr,1);
    $_SESSION['actions']=[];
    for ($i=0;$i<sizeof($arr);$i++){
        $concept = strtolower($arr[$i]);
        if(strstr($concept,'extending',true)){
            $concept = trim(strstr($concept,'extending',true));
        } else{
            $concept = trim(explode('{', $concept)[0]);
        }
        $action = new Action('Get all '.$concept.'s');
        $start=strpos($arr[$i],'{')+1;
        $end= strrpos($arr[$i],'}');
        $conceptBlock=substr($arr[$i],$start,$end);
        while(str_contains($conceptBlock,'{')){
            $first= trim(strstr($conceptBlock,'{',true));
            $last=substr($conceptBlock,strpos($conceptBlock,'}')+1);
            $conceptBlock=$first.$last;
        }
        print $conceptBlock;
        $propChunks = explode(':',$conceptBlock);
        array_pop($propChunks);
        foreach ($propChunks as $chunk){
            $fieldNameChunks = explode(' ',$chunk);
            if(is_array($fieldNameChunks)){
                $field = trim(array_pop($fieldNameChunks));
                while(strlen($field)===0){
                    $field = trim(array_pop($fieldNameChunks));
                }
                $action->addField($field);
            }
        }
        if($i===0){
            $action->selected = true;
        }
        $_SESSION['actions'][]=$action;
    }
} else if (isset($_POST['new-action-selected']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->selected = false;
        } else if ($_POST['new-action-selected'] == $_SESSION['actions'][$i]->name) {
            $_SESSION['actions'][$i]->selected = true;
        }
    }
} else session_destroy();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mouldit Code Generator</title>
</head>
<style>
    ul{
        list-style-type: none;
        padding: 0;
    }
    .selected {
        background: blue;
        color: antiquewhite;
    }
</style>
<body>
<div id="actions">
    <ul>
        <?php
        for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
            if ($_SESSION['actions'][$i]->selected || (!isset($_POST['new-action-selected']) && $i==0) ) {
                echo "<li class='selected'>" . $_SESSION['actions'][$i]->name . "</li>";
            } else echo "<li style='overflow:auto'>
                            <span style='float:left'>" . $_SESSION['actions'][$i]->name . "</span> 
                             <form style='float:right' action=\"" . $_SERVER['PHP_SELF'] . "\" method='post'>
                               <input  type='hidden' value='" . $_SESSION['actions'][$i]->name . "' name='action-name'>
                               <button type='submit' name='new-action-selected'>edit</button>
                            </form>
                         </li>";
        }
        ?>
    </ul>
</div>
<div id="detail">
    <!-- todo hier kan de gebruiker zijn API configureren
         todo toon hier de geselecteerde actie-->
</div>
</body>
</html>
<?php session_destroy();?>
