<?php
spl_autoload_register(function () {
    include 'showAction.php';
    include 'classes/Action.php';
    include 'classes/Concept.php';
    include 'classes/Field.php';
    include 'classes/FieldSet.php';
    include 'classes/SubFieldSet.php';
    include 'generate.php';
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
    include 'concepts.php';
    $_SESSION['concepts']=getConcepts($fileAsStr);
   // echo '<pre>'.print_r($_SESSION['concepts'], true).'</pre>';

    $_SESSION['actions'] = [];
    function fieldIsConcept($f){
        return $f->type!=='str'&&$f->type!=='int32'&&!str_contains($f->type,'=');
    }
    $selected=false;
    foreach ($_SESSION['concepts'] as $concept){
        $cpt=clone $concept;
        foreach ($implementedTypesOfActions as $actionType){
            $name=$actionType[0].' '.$cpt->name.'s';
            $action = new Action($name,$actionType[1],$actionType[0]);
            if(!$selected) {
                $action->selected=true;
                $selected=true;
            }
            $action->setFields($cpt->fields);
            $action->fieldset->setInclusivity(true);
            foreach ($action->fieldset->fields as $f){
                $f->setChecked(true);
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
                                    $fs=clone $_SESSION['concepts'][$i]->fields; // het gaat hier om een fieldset instance
                                    foreach ($fs->fields as $subf){
                                        $subf->setChecked(true);
                                    }
                                    $sfs=null;
                                    if($set instanceof SubFieldSet){
                                        // todo een mogelijk issue is dat een conceptName ook iets kan zijn als show extending content wat niet de bedoeling is => cut it away
                                        //      person komt hier nergens voor wat toch raar is, en movie en show komen enkel voor als content wat niet goed is!
                                        echo 'path='.$set->conceptPath;
                                        $sfs=new SubFieldSet($fs->conceptName,$set->conceptPath.'_'.$fs->conceptName);
                                    } else{
                                        echo 'name='.$set->conceptName;
                                        $sfs=new SubFieldSet($fs->conceptName,$set->conceptName.'_'.$fs->conceptName);
                                    }
                                    $sfs->setSubFields($fs->fields);
                                    $sfs->setInclusivity(true);
                                    $f->subfields=$sfs;
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
    //echo '<pre>'.print_r($_SESSION['actions'], true).'</pre>';
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
            for ($j = 0; $j < sizeof($_SESSION['actions'][$i]->fieldset->fields); $j++) {
                $_SESSION['actions'][$i]->fieldset->inclusivity = (bool)$_POST['fieldsConfig'];
                if (!isset($_POST[$_SESSION['actions'][$i]->fieldset->fields[$j]->name . 'Checked'])) {
                    $_SESSION['actions'][$i]->fieldset->fields[$j]->checked = false;
                } else {
                    $_SESSION['actions'][$i]->fieldset->fields[$j]->checked = true;
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
       showAction($_SESSION['actions'][$i]);
    }
    ?>
</div>
<script>
    // todo later toevoegen dat je geen zaken kan wijzigen zonder te bewaren zodat zeker alle wijzigen bewaard worden
    function checkFields(name) {
        const els = document.getElementsByTagName('input');
        for (let i = 0; i < els.length; i++) {
            if (els[i].type === 'checkbox' && !(els[i].checked) && els[i].name?.startsWith(name) && els[i].name?.endsWith('_checkbox')) els[i].checked = true;
        }
    }
    function uncheckFields(name) {
        const els = document.getElementsByTagName('input');
        for (let i = 0; i < els.length; i++) {
            if (els[i].type === 'checkbox' && (els[i].checked) && els[i].name?.startsWith(name) && els[i].name?.endsWith('_checkbox')) els[i].checked = false;
        }
    }
</script>
</body>
</html>
