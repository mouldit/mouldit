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
function fieldIsConcept($f){
    return $f->type!=='str'&&$f->type!=='int32'&&!str_contains($f->type,'=');
}
if (isset($_SESSION['pathToRootOfServer']) &&
    $dir = opendir($_SESSION['pathToRootOfServer']) &&
        file_exists($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl') &&
        !isset($_SESSION['actions'])) {
    global $implementedTypesOfActions;
    $implementedTypesOfActions= [
        ['Get_all','get']
    ];
    $fileAsStr = file_get_contents($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl');
    // todo later aanvullen met regExp die maakt dat er meer dan één spatie tussen abstract en type mag zijn
    $fileAsStr = strtolower($fileAsStr);
    include 'concepts.php';
    $_SESSION['concepts']=getConcepts($fileAsStr);
    //echo '<pre>'.print_r($_SESSION['concepts'], true).'</pre>';

    $_SESSION['actions'] = [];

    $selected=false;
    foreach ($_SESSION['concepts'] as $concept){
        foreach ($implementedTypesOfActions as $actionType){
            $cpt=clone $concept;
            $name=$actionType[0].'_'.$cpt->name.'s';
            $action = new Action($name,$actionType[1],$actionType[0]);
            if(!$selected) {
                $action->selected=true;
                $selected=true;
            }
            // todo dit zet het fieldset mmaar hierna wordt dit gewijzigd en dit reflecteert al meteen in de concepts session var
            //      verklaring: cloning gebeurt net als bij js oppervlakkig
            $action->setFields($cpt->fields);
            $action->fieldset->setInclusivity(true);
            foreach ($action->fieldset->fields as $f){
                $f->setChecked(true);
            }
            $subFieldSetsToProcess=[$action->fieldset];
            $action->activate();
            $newSubFieldSets=[];
            //echo '<br><pre> dit zijn de concepts die in principe elke iteratie aan zichzelf gelijk zouden moeten blijevn<br>'.print_r($_SESSION['concepts'], true).'</pre>';
            while(sizeof($subFieldSetsToProcess)>0){
                foreach ($subFieldSetsToProcess as $set){
                    foreach ($set->fields as $f){
                        // dit zijn Fields
                        if(fieldIsConcept($f)){
                            for ($i=0;$i<sizeof($_SESSION['concepts']);$i++){
                                if($_SESSION['concepts'][$i]->name===$f->type){
                                    // het gaat hier om een fieldset instance $fs
                                    $fs=clone ($_SESSION['concepts'][$i]->fields);
                                    foreach ($fs->fields as $subf){
                                        $subf->setChecked(true);
                                    }
                                    $sfs=null;
                                    if($set instanceof SubFieldSet){
                                        $sfs=new SubFieldSet($fs->conceptName,$set->conceptPath.'_'.$fs->conceptName,$set->fieldPath.'_'.$f->name);
                                    } else{
                                        // todo fix!
                                        $sfs=new SubFieldSet($fs->conceptName,$set->conceptName.'_'.$fs->conceptName,$f->name);
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
            //echo '<pre> dit is er aanwezig <br>'.print_r($_POST, true).'</pre>';
            $_SESSION['actions'][$i]->active = $_POST['isActive'];
            $subFieldSetsToProcess=[$_SESSION['actions'][$i]->fieldset];
            $newSubFieldSets=[];
            while(sizeof($subFieldSetsToProcess)>0){
                foreach ($subFieldSetsToProcess as $set){
                    if($set instanceof FieldSet){
                        $set->inclusivity= (bool)$_POST[$_SESSION['actions'][$i]->name.'_fieldsConfig'];
                        foreach ($set->fields as $f){
                            if (isset($_POST[$_SESSION['actions'][$i]->name.'_checkbox_'.$f->name])) {
                                $f->checked = true;
                            } else {
                                $f->checked = false;
                            }
                            if(fieldIsConcept($f)){
                                $newSubFieldSets[]=$f->subfields;
                            }
                        }
                    } else{
                        $set->inclusivity= (bool)$_POST[$_SESSION['actions'][$i]->name.'_fieldsConfig_'.$set->fieldPath];
                        foreach ($set->fields as $f){
                            if (isset($_POST[$_SESSION['actions'][$i]->name.'_checkbox_'.$set->fieldPath.'_'.$f->name])) {
                                $f->checked = true;
                            } else {
                                $f->checked = false;
                            }
                            if(fieldIsConcept($f)){
                                $newSubFieldSets[]=$f->subfields;
                            }
                        }
                    }
                }
                $subFieldSetsToProcess = $newSubFieldSets;
                $newSubFieldSets=[];
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
    // todo : de bewaarde configuratie genereren in code (dwz generate functie aanpassen inzake de subfields
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
            if (els[i].type === 'checkbox'
                && !(els[i].checked)
                && els[i].name?.startsWith(name)) {
                if(els[i].name.split(name)[1].trim().split('_').length===1) els[i].checked = true
            }
        }
    }
    function uncheckFields(name) {
        const els = document.getElementsByTagName('input');
        for (let i = 0; i < els.length; i++) {
            if (els[i].type === 'checkbox' && (els[i].checked) && els[i].name?.startsWith(name)){

                if(els[i].name.split(name)[1].trim().split('_').length===1) els[i].checked = false
            }
        }
    }
</script>
</body>
</html>
