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
    // todo vul fieldset aan voor extending concepts met die van het overeenkomstige abstracte concept
    $_SESSION['concepts']=getConcepts($fileAsStr);
    $_SESSION['actions'] = [];
    function fieldIsConcept($f){
        return $f->type!=='str'&&$f->type!=='int32'&&!str_contains($f->type,'=');
    }
    foreach ($_SESSION['concepts'] as $concept){
        foreach ($implementedTypesOfActions as $actionType){
            $action = new Action($actionType[0].$concept->name.'s',$actionType[1],$actionType[0],$concept->fieldset);
            $action->fieldset->setInclusivity(true);
            foreach ($action->fieldset as $f){
                $f->checked = true;
            }
            $subFieldSetsToProcess=[$action->fieldset];
            $action->activate();
            $newSubFieldSets=[];
            while(sizeof($subFieldSetsToProcess)>0){
                foreach ($subFieldSetsToProcess as $set){
                    foreach ($set->fields as $f){
                        if(fieldIsConcept($f)){
                            for ($i=0;$i<sizeof($_SESSION['concepts']);$i++){
                                if($_SESSION['concepts'][$i]->name===$f->type){
                                    $fs=new FieldSet($_SESSION['concepts'][$i]->fields);
                                    $fs->setInclusivity(true);
                                    foreach ($fs->fields as $subf){
                                        $subf->checked = true;
                                    }
                                    $f->subfields = new SubFieldSet($fs);
                                    $newSubFieldSets[]=$f->subfields;
                                    break;
                                }
                            }
                        }
                    }
                }
                $subFieldSetsToProcess = $newSubFieldSets;
                $newSubFieldSets=[];
            }
            $_SESSION['actions'][]=$action;
        }
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
    $_SESSION['actions'] = $arrReOrdered;
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
