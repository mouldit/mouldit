<?php
spl_autoload_register(function () {
    include 'Action.php';
    include 'generate.php';
    include 'concepts.php';
});
session_start();
if (isset($_SESSION['pathToRootOfServer']) &&
    $dir = opendir($_SESSION['pathToRootOfServer']) &&
        file_exists($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl') &&
        !isset($_SESSION['actions'])) {


    global $implementedTypesOfActions;
    $implementedTypesOfActions= [
        ['Get all','get']
    ];
    $fileAsStr = file_get_contents($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl');
    // todo later aanvullen met regExp die maakt dat er meer dan één spatie tussen abstract en type mag zijn
    $fileAsStr = strtolower($fileAsStr);
    $_SESSION['concepts']=getConcepts($fileAsStr);
    // todo
    $_SESSION['actions'] = [];


    function addFields(&$action, $next){
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
                $action->addField($fieldName, 'include', true, $type);
            }
        }
    }
    function processAbstractConcept($next): void{
        global $implementedTypesOfActions;
        $concept = trim(strstr($next, '{', true));
        $_SESSION['concepts'][]=$concept;
        $action = new Action('Get all ' . $concept . 's',$implementedTypesOfActions[0][1],$implementedTypesOfActions[0][0]);
        addFields($action, $next);
        $_SESSION['actions'][] = $action;
    }
    if ($next) {

    }
    $arr = explode('type', $fileAsStr);
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
    $arrReOrdered = [];
    $index = null;
    for ($j = 0; $j < sizeof($_SESSION['actions']); $j++){
        if($_SESSION['actions'][$j]->selected){
            $index = $j;
            $arrReOrdered[]=$_SESSION['actions'][$j];
        } else if($index && $j>$index){
            $arrReOrdered[]=$_SESSION['actions'][$j];
        }
    }
    for ($j = 0; $j < sizeof($_SESSION['actions']); $j++){
        if($j<$index){
            $arrReOrdered[]=$_SESSION['actions'][$j];
        }
        if($j>=$index) break;
    }
    function getSubFields($field){
        if($field[3]!=='str'&&$field[3]!=='int32'&&!str_contains($field[3],'=')){

        }
    }
    $_SESSION['actions'] = $arrReOrdered;
    for ($i=0;$i<sizeof($_SESSION['actions']);$i++){
        for ($j=0;$j<sizeof($_SESSION['actions'][$i]->fields);$j++){

        }
    }
    // todo per actie en per fields voeg subfields toe met addSubfield method en params:
    //
    print_r($_SESSION['actions']);
} else if (isset($_POST['new-action-selected']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->selected = false;
        } else if ($_POST['action-name'] === $_SESSION['actions'][$i]->name) {
            $_SESSION['actions'][$i]->selected = true;
        }
    }
} else if (isset($_POST['action-edited']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->active = $_POST['isActive'];
            for ($j = 0; $j < sizeof($_SESSION['actions'][$i]->fields); $j++) {
                $_SESSION['actions'][$i]->fields[$j][1] = $_POST['fieldsConfig'];
                if (!isset($_POST[$_SESSION['actions'][$i]->fields[$j][0] . 'Checked'])) {
                    $_SESSION['actions'][$i]->fields[$j][2] = false;
                } else {
                    $_SESSION['actions'][$i]->fields[$j][2] = true;
                }
            }
        }
    }
} else if(isset($_POST['generate']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    generate($_SESSION['concepts'],$_SESSION['actions'],$_SESSION['pathToRootOfServer']);
}else session_destroy();
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
            $part .= '<div><label><input onchange="checkFields()" type="radio" name="fieldsConfig" value="include"';
            if ($_SESSION['actions'][$i]->fields[0][1] === 'include') {
                $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields()" type="radio" name="fieldsConfig" value="exclude"> Exclude</label></div>';
            } else {
                $part .= '> Include</label>
                    <label><input type="radio" name="fieldsConfig" value="exclude" checked> Exclude</label>
            </div>';
            }
            for ($j = 0; $j < sizeof($_SESSION['actions'][$i]->fields); $j++) {
                $part .= '<div><label>' . $_SESSION['actions'][$i]->fields[$j][0] . '<input type="checkbox" name="' . $_SESSION['actions'][$i]->fields[$j][0] . 'Checked" value="1"';
                if ($_SESSION['actions'][$i]->fields[$j][2]) {
                    $part .= ' checked></label></div>';
                } else {
                    $part .= '></label></div>';
                }
            }
            $part .= '<div><button type="submit" name="action-edited">save</button></div>
</form><br><div><form style="float:right;" action="' . $_SERVER['PHP_SELF'] . '" method="post"><input type="hidden" name="generate"><button type="submit">Generate</button></form></div>';
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
        for (let i = 0; i < els.length; i++) {
            if (els[i].type === 'checkbox' && !(els[i].checked)) els[i].checked = true;
        }
    }

    function uncheckFields() {
        const els = document.getElementsByTagName('input');
        for (let i = 0; i < els.length; i++) {
            if (els[i].type === 'checkbox' && (els[i].checked)) els[i].checked = false;
        }
    }
</script>
</body>
</html>
