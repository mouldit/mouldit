<?php
spl_autoload_register(function () {
    include 'showAction.php';
    include 'showPage.php';
    include 'showComponent.php';
    include 'classes/frontend-methods.php';
    include 'classes/Frontend.php';
    include 'classes/IComponent.php';
    include 'classes/IPage.php';
    include 'classes/Action.php';
    include 'classes/Component.php';
    include 'classes/components/Menubar/Menubar.php';
    include 'classes/components/Menubar/MenuItem.php';
    include 'classes/components/Card/Card.php';
    include 'classes/Page.php';
    include 'classes/Concept.php';
    include 'classes/Field.php';
    include 'classes/FieldSet.php';
    include 'classes/SubFieldSet.php';
    include 'generateBackend.php';
   // include 'generateFrontend.php'; // todo deze moet uiteindelijk wegzijn
});
session_start();
global $implementedTypesOfComponents;
$implementedTypesOfComponents = ['card', 'menubar', 'table'];
// frontend
if (isset($_SESSION['pathToRootOfClient'])) {
    if (isset($_POST['generate-frontend']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $_SESSION['frontend']->generate($_SESSION['pathToRootOfClient'].'/src/app');
    }
}
// backend
if (isset($_SESSION['pathToRootOfServer']) &&
    $dir = opendir($_SESSION['pathToRootOfServer']) &&
        file_exists($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl') &&
        !isset($_SESSION['actions'])) {
    echo 'creating actions';
    $_SESSION['pageCounter']=0;
    $_SESSION['componentCounter']=0;
    global $implementedTypesOfActions;
    $implementedTypesOfActions = [
        ['Get_all', 'get', '/get/all/']
    ];
    $fileAsStr = file_get_contents($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl');
    // todo later aanvullen met regExp die maakt dat er meer dan één spatie tussen abstract en type mag zijn
    $fileAsStr = strtolower($fileAsStr);
    include 'concepts.php';
    $_SESSION['concepts'] = getConcepts($fileAsStr);
    //echo '<pre>'.print_r($_SESSION['concepts'], true).'</pre>';

    $_SESSION['actions'] = [];
    $selected = false;
    foreach ($_SESSION['concepts'] as $concept) {
        foreach ($implementedTypesOfActions as $actionType) {
            $cpt = clone $concept;
            $name = $actionType[0] . '_' . $cpt->name . 's';
            // todo sommige verbs daar moet nog /:id achter! hetgeen dan automtisch in de Mouldit frontend een id zal krijgen via de angular generated code
            $action = new Action($name, $actionType[1], $actionType[0], $actionType[2] . $cpt->name, $cpt->name);
            if (!$selected) {
                $action->selected = true;
                $selected = true;
            }
            $action->setFields($cpt->fields);
            $action->fieldset->setInclusivity(true);
            foreach ($action->fieldset->fields as $f) {
                $f->setChecked(true);
            }
            $subFieldSetsToProcess = [$action->fieldset];
            $action->activate();
            $newSubFieldSets = [];
            //echo '<br><pre> dit zijn de concepts die in principe elke iteratie aan zichzelf gelijk zouden moeten blijevn<br>'.print_r($_SESSION['concepts'], true).'</pre>';
            while (sizeof($subFieldSetsToProcess) > 0) {
                foreach ($subFieldSetsToProcess as $set) {
                    foreach ($set->fields as $f) {
                        if ($set instanceof SubFieldSet) $f->fieldPath = $set->fieldPath . '_' . $f->name; else $f->fieldPath = $f->name;
                        if ($f->isConcept()) {
                            for ($i = 0; $i < sizeof($_SESSION['concepts']); $i++) {
                                if ($_SESSION['concepts'][$i]->name === $f->type) {
                                    // het gaat hier om een fieldset instance $fs
                                    $fs = clone($_SESSION['concepts'][$i]->fields);
                                    foreach ($fs->fields as $subf) {
                                        $subf->setChecked(true);
                                    }
                                    $sfs = null;
                                    if ($set instanceof SubFieldSet) {
                                        $sfs = new SubFieldSet($fs->conceptName, $set->conceptPath . '_' . $fs->conceptName, $set->fieldPath . '_' . $f->name);
                                    } else {
                                        $sfs = new SubFieldSet($fs->conceptName, $set->conceptName . '_' . $fs->conceptName, $f->name);
                                    }
                                    $sfs->setSubFields($fs->fields);
                                    $sfs->setInclusivity(true);
                                    $f->subfields = $sfs;
                                    $newSubFieldSets[] = $f->subfields;
                                    break;
                                }
                            }
                        }
                    }
                }
                $subFieldSetsToProcess = $newSubFieldSets;
                $newSubFieldSets = [];
            }
            $_SESSION['actions'][] = $action;
        }
    }
    //echo '<pre>'.print_r($_SESSION['actions'], true).'</pre>';
    $_SESSION['frontend']  = new Frontend() ;
    $selected = false;
    $main = new Page($_SESSION['pageCounter']++, 'main_page', '', true);
    $main->select();
    // todo add a router outlet component by default
    $_SESSION['frontend']->pages[] = $main;
    foreach ($_SESSION['actions'] as $a) {
        $p = new Page($_SESSION['pageCounter']++, $a->name . '_page', $a->clientURL);
        $p->actionLink = $a->name;
        $p->parentId=$main->id;
        $_SESSION['frontend']->pages[] = $p;
    }
} else if (isset($_POST['new-action-selected']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->selected = false;
        } else if ($_POST['action-name'] === $_SESSION['actions'][$i]->name) {
            $_SESSION['actions'][$i]->selected = true;
        }
    }
} else if (isset($_POST['new-page-selected']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            $_SESSION['frontend']->pages[$i]->selected = false;
        } else if ($_POST['page-name'] === $_SESSION['frontend']->pages[$i]->name) {
            $_SESSION['frontend']->pages[$i]->selected = true;
        }
    }
} else if (isset($_POST['new-component-selected']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    $_SESSION['frontend']->pages[$i]->components[$j]->deselect();
                    break;
                }
            }
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->name === $_POST['component-name']) {
                    $_SESSION['frontend']->pages[$i]->components[$j]->select();
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['action-edited']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['actions']); $i++) {
        if ($_SESSION['actions'][$i]->selected) {
            $_SESSION['actions'][$i]->active = $_POST['isActive'];
            $subFieldSetsToProcess = [$_SESSION['actions'][$i]->fieldset];
            $newSubFieldSets = [];
            while (sizeof($subFieldSetsToProcess) > 0) {
                foreach ($subFieldSetsToProcess as $set) {
                    if ($set instanceof FieldSet) {
                        $set->inclusivity = (bool)$_POST[$_SESSION['actions'][$i]->name . '_fieldsConfig'];
                        foreach ($set->fields as $f) {
                            if (isset($_POST[$_SESSION['actions'][$i]->name . '_checkbox_' . $f->name])) {
                                $f->checked = true;
                            } else {
                                $f->checked = false;
                            }
                            if ($f->isConcept()) {
                                $newSubFieldSets[] = $f->subfields;
                            }
                        }
                    } else {
                        $set->inclusivity = (bool)$_POST[$_SESSION['actions'][$i]->name . '_fieldsConfig_' . $set->fieldPath];
                        foreach ($set->fields as $f) {
                            if (isset($_POST[$_SESSION['actions'][$i]->name . '_checkbox_' . $set->fieldPath . '_' . $f->name])) {
                                $f->checked = true;
                            } else {
                                $f->checked = false;
                            }
                            if ($f->isConcept()) {
                                $newSubFieldSets[] = $f->subfields;
                            }
                        }
                    }
                }
                $subFieldSetsToProcess = $newSubFieldSets;
                $newSubFieldSets = [];
            }
        }
    }
} else if (isset($_POST['page-edited']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            $_SESSION['frontend']->pages[$i]->name = $_POST['name'];
            $_SESSION['frontend']->pages[$i]->url = $_POST['url'];
            if (isset($_POST['action']) && isset($_POST['target'])) {
                for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                    if ($_SESSION['frontend']->pages[$i]->components[$j]->id === (int)$_POST['target']) {
                        for ($k = 0; $k < sizeof($_SESSION['actions']); $k++) {
                            if ($_SESSION['actions'][$k]->name === $_POST['action']) {
                                $_SESSION['frontend']->pages[$i]->components[$j]->linkWithAction($_SESSION['actions'][$k]);
                                break;
                            }
                        }
                        break;
                    }
                }
            }
            if (isset($_POST['action'])) $_SESSION['frontend']->pages[$i]->actionLink = $_POST['action'];
            break;
        }
    }
} else if (isset($_POST['component-edited']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    if (isset($_POST['component-name'])) {
                        $_SESSION['frontend']->pages[$i]->components[$j]->name = $_POST['component-name'];
                    }
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['mapping']) && isset($_POST['component']) && isset($_POST['page']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->id === (int)$_POST['page']) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->id === (int)$_POST['component']) {
                    for ($k = 0; $k < sizeof($_SESSION['actions']); $k++) {
                        if ($_SESSION['actions'][$k]->name === $_SESSION['frontend']->pages[$i]->actionLink) {
                            $props = $_SESSION['frontend']->pages[$i]->components[$j]->getAttributes();
                            $_SESSION['frontend']->pages[$i]->components[$j]->mapping = [];
                            $fieldNames = $_SESSION['actions'][$k]->getFullQualifiedFieldNames();
                            foreach ($props as $prop){
                                $val = NULL;
                                for ($l=0;$l<sizeof($fieldNames);$l++){
                                    if(isset($_POST[$fieldNames[$l]]) && $_POST[$fieldNames[$l]]===$prop){
                                        $val = $fieldNames[$l];
                                        break;
                                    }
                                }
                                $_SESSION['frontend']->pages[$i]->components[$j]->mapping[$prop]=$val;
                            }
                            //echo '<pre>'.print_r($_SESSION['frontend']->pages[$i]->components[$j]->mapping, true).'</pre>';
                            break;
                        }
                    }
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['remove']) && isset($_POST['remove-item']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    for ($k = 0; $k < sizeof($_SESSION['frontend']->pages[$i]->components[$j]->menuItems); $k++) {
                        if ($_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->name === $_POST['remove-item']) {
                            $rang = $_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->number;
                            array_splice($_SESSION['frontend']->pages[$i]->components[$j]->menuItems, $k, 1);
                            for ($k = 0; $k < sizeof($_SESSION['frontend']->pages[$i]->components[$j]->menuItems); $k++) {
                                if ($_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->number > $rang) {
                                    $_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->number--;
                                }
                            }
                            break;
                        }
                    }
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['add-item']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    if (isset($_POST['item-name']) && isset($_POST['page'])) {
                        $nmbr = sizeof($_SESSION['frontend']->pages[$i]->components[$j]->menuItems) + 1;
                        $_SESSION['frontend']->pages[$i]->components[$j]->menuItems[] = new \components\Menubar\MenuItem($_POST['item-name'], $_POST['page'], $nmbr);
                    }
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['save-item']) && isset($_POST['edit-item']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    for ($k = 0; $k < sizeof($_SESSION['frontend']->pages[$i]->components[$j]->menuItems); $k++) {
                        if ($_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->number == $_POST['edit-item']) {
                            if (isset($_POST['menu-item-number'])) {
                                $_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->number = $_POST['menu-item-number'];
                            }
                            if (isset($_POST['menu-item-name'])) {
                                $_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->name = $_POST['menu-item-name'];
                            }
                            if (isset($_POST['page'])) {
                                $_SESSION['frontend']->pages[$i]->components[$j]->menuItems[$k]->page = $_POST['page'];
                            }
                            break;
                        }
                    }
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['add']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            $counter = 0;
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->type == $_POST['add-component']) $counter++;
            }
            $comp = NULL;
            switch ($_POST['add-component']) {
                case 'menubar':
                    if ($_SESSION['frontend']->pages[$i]->main) {
                        $menuItems = [];
                        for ($j = 0; $j < sizeof($_SESSION['concepts']); $j++) {
                            // per concept ga je indien er een getAllActie voor bestaat een default menu item krijgen
                            for ($k = 0; $k < sizeof($_SESSION['actions']); $k++) {
                                if ($_SESSION['actions'][$k]->type === 'Get_all' && $_SESSION['actions'][$k]->concept === $_SESSION['concepts'][$j]->name) {
                                    for ($l = 0; $l < sizeof($_SESSION['frontend']->pages); $l++) {
                                        if (isset($_SESSION['frontend']->pages[$l]->actionLink) && $_SESSION['frontend']->pages[$l]->actionLink === $_SESSION['actions'][$k]->name) {
                                            $menuItems[] = new \components\Menubar\MenuItem($_SESSION['concepts'][$j]->name . 's',
                                                $_SESSION['frontend']->pages[$l]->id
                                                , $j + 1);
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        $comp = new \components\Menubar\Menubar($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id, $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter,
                            $_POST['add-component'], $menuItems
                        );
                    } else {
                        $comp = new \components\Menubar\Menubar($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id, $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter,
                            $_POST['add-component']);
                    }
                    break;
                case 'table':
                    break;
                case 'card':

                    $comp = new \components\Card\Card($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id, $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter,
                        $_POST['add-component']);
                    break;
            }
            $_SESSION['frontend']->pages[$i]->addComponent($comp);
            break;
        }
    }
    // todo maak dat je een pagina kan toevoegen en verwijderen, nu enkel aanpassen
    // todo maak dat je componenten een andere volgorde kan geven zodat je ze niet helemaal moet verwijderen en opnieuw bouwen
} else if (isset($_POST['generate-backend']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    generateBackend($_SESSION['concepts'], $_SESSION['actions'], $_SESSION['pathToRootOfServer']);
} else if(!isset($_POST['generate-frontend'])){
    echo 'destroying session';
    session_destroy();
}
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
<div id="action-detail" style="float:left; min-width: 500px;min-height:400px;border:1px solid red">
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
            if (els[i].type === 'checkbox'
                && !(els[i].checked)
                && els[i].name?.startsWith(name)) {
                if (els[i].name.split(name)[1].trim().split('_').length === 1) els[i].checked = true
            }
        }
    }

    function uncheckFields(name) {
        const els = document.getElementsByTagName('input');
        for (let i = 0; i < els.length; i++) {
            if (els[i].type === 'checkbox' && (els[i].checked) && els[i].name?.startsWith(name)) {

                if (els[i].name.split(name)[1].trim().split('_').length === 1) els[i].checked = false
            }
        }
    }
</script>
<div id="pages" style="float:left; min-width: 200px;border:1px solid red">
    <ul style="margin:0">
        <?php
        for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
            if ($_SESSION['frontend']->pages[$i]->selected) {
                echo "<li class='selected'>" . $_SESSION['frontend']->pages[$i]->name . "</li>";
            } else echo "<li style='overflow:auto'>
                            <span style='float:left'>" . $_SESSION['frontend']->pages[$i]->name . "</span> 
                             <form style='float:right' action=\"" . $_SERVER['PHP_SELF'] . "\" method='post'>
                               <input  type='hidden' value='" . $_SESSION['frontend']->pages[$i]->name . "' name='page-name'>
                               <button type='submit' name='new-page-selected'>edit</button>
                            </form>
                         </li>";
        }
        ?>
    </ul>
</div>
<div id="page-detail" style="float:left; min-width: 500px;min-height:400px;border:1px solid red;padding: 0 8px">
    <?php
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        showPage($_SESSION['frontend']->pages[$i], $_SESSION['actions'], $implementedTypesOfComponents);
    }
    ?>
</div>
<div id="component-detail" style="float:left; min-width: 700px;min-height:400px;border:1px solid red;padding: 0 8px">
    <?php
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    showComponent($_SESSION['frontend']->pages[$i]->components[$j], $_SESSION['frontend']->pages);
                    break;
                }
            }
            break;
        }
    }
    ?>
</div>
</body>
</html>
