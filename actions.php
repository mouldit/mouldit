<?php
spl_autoload_register(function () {
    include 'Action.php';
});
session_start();
global $implementedTypesOfActions;
if (isset($_SESSION['pathToRootOfServer']) &&
    $dir = opendir($_SESSION['pathToRootOfServer']) &&
        file_exists($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl') &&
        !isset($_SESSION['actions'])) {
    $implementedTypesOfActions = [
        'GET ALL'
    ];
    $fileAsStr = file_get_contents($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl');
    // todo strategie
    // ga doorheen de tekst per type concept: abstract, extending en gewoon en zet dit er ook bij in een aparte arr:
    // deze arr bevat de concept naam en welk van de 3 types er zijn en de bijhorende properties
    // bij het invullen van de actions array kan je dan de bijhorende props nemen voor de verschillende concepten die
    // extenden van een abstract concept

    // aanpak, je doet onderstaande oefening om elke type concept eruit te halen met zijn props

    // abstracte concepten
    // todo later aanvullen met regExp die maakt dat er meer dan één spatie tussen abstract en type mag zijn

    // code blokje van eerstvolgende abstract concept
    $next = strstr($fileAsStr,'abstract type');
    function getNext($next){
        $next = substr($next,strlen('abstract type'));
        $posType = strpos($next,'type');
        $posAbstractType=  strpos($next,'abstract type');
        if($posType>$posAbstractType){
            $next = trim(substr($next,0,$posAbstractType));
        } else{
            $next = trim(substr($next,0,$posType));
        }
        return $next;
    }
    function processNext($next){
        // todo process $next qua naam en properties en bewaar in een data structuur

    }
    if($next){
        $next = getNext($next);
        processNext($next);
        // we zoeken nu een eerst volgende blokje van een abstract concept
        $expl = explode($fileAsStr,$next);
        while(sizeof($expl)>1 && $next = strstr($expl[1],'abstract type')){
            $next = getNext($next);
            processNext($next);
            $expl = explode($fileAsStr,$next);
        }
    }
    // concepten die extenden van een abstract concept met de naam van het abstracte concept
    // todo
    // gewone concepten
    // todo
    $arr = explode('type', $fileAsStr);
    $arr = array_slice($arr, 1);
    $arrConcepts = [];
    $_SESSION['actions'] = [];
    for ($i = 0; $i < sizeof($arr); $i++) {
        $concept = strtolower($arr[$i]);

        if (strstr($concept, 'extending', true)) {
            $concept = trim(strstr($concept, 'extending', true));
        } else {
            $concept = trim(explode('{', $concept)[0]);
        }
        $action = new Action('Get all ' . $concept . 's');
        $start = strpos($arr[$i], '{') + 1;
        $end = strrpos($arr[$i], '}');
        $conceptBlock = substr($arr[$i], $start, $end);
        while (str_contains($conceptBlock, '{')) {
            $first = trim(strstr($conceptBlock, '{', true));
            $last = substr($conceptBlock, strpos($conceptBlock, '}') + 1);
            $conceptBlock = $first . $last;
        }
        $propChunks = explode(':', $conceptBlock);
        array_pop($propChunks);
        // todo zorg dat concepten die extenden de velden van het overkoepelende abstracte type overnemen
        foreach ($propChunks as $chunk) {
            $fieldNameChunks = explode(' ', $chunk);
            if (is_array($fieldNameChunks)) {
                $field = trim(array_pop($fieldNameChunks));
                while (strlen($field) === 0) {
                    $field = trim(array_pop($fieldNameChunks));
                }
                $action->addField($field,'include',true);
            }
        }
        if ($i === 0) {
            $action->selected = true;
        }
        $_SESSION['actions'][] = $action;
    }
} else if (isset($_POST['new-action-selected']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->selected = false;
        } else if ($_POST['action-name'] === $_SESSION['actions'][$i]->name) {
            $_SESSION['actions'][$i]->selected = true;
        }
    }
} else if(isset($_POST['action-edited']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->active = $_POST['isActive'];
            for ($j=0;$j<sizeof($_SESSION['actions'][$i]->fields);$j++){
                $_SESSION['actions'][$i]->fields[$j][1]=$_POST['fieldsConfig'];
                if(!isset($_POST[$_SESSION['actions'][$i]->fields[$j][0].'Checked'])){
                    $_SESSION['actions'][$i]->fields[$j][2]=false;
                } else{
                    $_SESSION['actions'][$i]->fields[$j][2]=true;
                }
            }
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
    ul {
        list-style-type: none;
        padding: 0;
    }

    .selected {
        background: blue;
        color: antiquewhite;
    }
</style>
<body>
<div id="actions" style="float:left; min-width: 200px;border:1px solid red">
    <ul style="margin:0">
        <?php
        for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
            if ($_SESSION['actions'][$i]->selected) {
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
<div id="detail" style="float:left; min-width: 500px;min-height:400px;border:1px solid red">
    <?php
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        $part = '';
        if ($_SESSION['actions'][$i]->selected) {
            $part .= '<h2 style="margin: 0">Configure backend of action: ' . $_SESSION['actions'][$i]->name . ' 
       </h2>
       <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div><label><input type="radio" name="isActive" value="1"';
            if ($_SESSION['actions'][$i]->active) {
                $part .= ' checked> ON</label>
                    <label><input type="radio" name="isActive" value="0"> OFF</label></div>';
            } else {
                $part .= '> ON</label>
                    <label><input type="radio" name="isActive" value="0" checked> OFF</label>
            </div>';
            }
            $part.='<div><label><input onchange="checkFields()" type="radio" name="fieldsConfig" value="include"';
            if ($_SESSION['actions'][$i]->fields[0][1]==='include') {
                $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields()" type="radio" name="fieldsConfig" value="exclude"> Exclude</label></div>';
            } else {
                $part .= '> Include</label>
                    <label><input type="radio" name="fieldsConfig" value="exclude" checked> Exclude</label>
            </div>';
            }
            for ($j=0;$j<sizeof($_SESSION['actions'][$i]->fields);$j++){
                $part.='<div><label>'.$_SESSION['actions'][$i]->fields[$j][0].'<input type="checkbox" name="'.$_SESSION['actions'][$i]->fields[$j][0].'Checked" value="1"';
                if($_SESSION['actions'][$i]->fields[$j][2]){
                    $part .= ' checked></label></div>';
                } else{
                    $part .= '></label></div>';
                }
            }
           $part.='<div><button type="submit" name="action-edited">save</button></div>
</form>';
            echo $part;
            break;
        }
    }
    ?>
</div>
<script>
    // todo later toevoegen dat je geen zaken kan wijzigen zonder te bewaren zodat zeker alle wijzigen bewaard worden
    function checkFields() {
        const els = document.getElementsByTagName('input');
        for (let i=0;i<els.length;i++){
            if(els[i].type==='checkbox' && !(els[i].checked)) els[i].checked = true;
        }
    }
    function uncheckFields() {
        const els = document.getElementsByTagName('input');
        for (let i=0;i<els.length;i++){
            if(els[i].type==='checkbox' && (els[i].checked)) els[i].checked = false;
        }
    }
</script>
</body>
</html>
