<?php
spl_autoload_register(function () {
    require_once('showAction.php');
    require_once('showPage.php');
    require_once('classes/frontend-methods.php');
    require_once('classes/Frontend.php');
    require_once('showComponent.php');
    require_once('classes/Enums/IconPositionType.php');
    require_once('classes/Enums/IconType.php');
    require_once('classes/Enums/DisplayType.php');
    require_once('classes/Enums/TriggerType.php');
    require_once('classes/Enums/PageTriggerType.php');
    require_once('classes/Enums/OverflowType.php');
    require_once('classes/Enums/UnitType.php');
    require_once('classes/Action.php');
    require_once('classes/Effect.php');
    require_once('classes/components/Component.php');
    require_once('classes/components/IComponent.php');
    require_once('classes/components/Icon.php');
    require_once('classes/components/groups/Dimensioning.php');
    require_once('classes/components/groups/Visibility.php');
    require_once('classes/components/Menubar/Menubar.php');
    require_once('classes/components/Menubar/MenuItem.php');
    require_once('classes/components/Card/Card.php');
    require_once("classes/components/Button/Button.php");
    require_once('classes/components/groups/ContentInjection.php');
    require_once('classes/components/groups/Dimension.php');
    require_once('classes/components/groups/ContentInjection.php');
    require_once('classes/IPage.php');
    require_once('classes/Page.php');
    require_once('classes/Concept.php');
    require_once('classes/Field.php');
    require_once('classes/FieldSet.php');
    require_once('classes/SubFieldSet.php');
    require_once('generateBackend.php');
});
session_start();
global $implementedTypesOfComponents;
$implementedTypesOfComponents = ['card', 'menubar', 'table', 'button'];
// frontend
if (isset($_SESSION['pathToRootOfClient'])) {
    if (isset($_POST['generate-frontend']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['frontend']->generate($_SESSION['pathToRootOfClient'] . '/src/app');
    }
}
// backend
if (isset($_SESSION['pathToRootOfServer']) &&
    $dir = opendir($_SESSION['pathToRootOfServer']) &&
        file_exists($_SESSION['pathToRootOfServer'] . '/dbschema/default.esdl') &&
        !isset($_SESSION['actions'])) {
    $_SESSION['pageCounter'] = 0;
    $_SESSION['componentCounter'] = 0;
    $_SESSION['effectCounter'] = 0;
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
    $_SESSION['frontend'] = new Frontend();
    $selected = false;
    $main = new Page($_SESSION['pageCounter']++, 'main_page', '', true);
    $main->select();
    // todo add a router outlet component by default
    $_SESSION['frontend']->pages[] = $main;
    foreach ($_SESSION['actions'] as $a) {
        $p = new Page($_SESSION['pageCounter']++, $a->name . '_page', $a->clientURL);
        $p->parentId = $main->id;
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
                if ($_SESSION['frontend']->pages[$i]->components[$j]->id === (int)$_POST['component-id']) {
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
    // todo
    //      voor id van effecten kan je op termijn ook een id genereren op basis van id component en id page combined en dan een nummer per effect binnen de component zelf
    //      het id is dan een string
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            $_SESSION['frontend']->pages[$i]->name = $_POST['name'];
            $_SESSION['frontend']->pages[$i]->url = $_POST['url'];
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
} else if (isset($_POST['save-ci']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // todo fix: bij elke nieuwe create wordt voor elk prop de oude waarde overschreven
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                // todo dit is niet per se de geselecteerde component
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected && (int)$_POST['save-ci'] === $_SESSION['frontend']->pages[$i]->components[$j]->id) {
                    $keys = array_keys($_POST);
                    foreach ($keys as $item) {
                        if(str_contains($item,'ci_')){
                            // de waarde is nu een type dat je moet aanmaken en toevoegen aan ci prop
                            switch ($_POST[$item]){
                                case 'menubar':
                                    // todo
                                    break;
                                case 'table':
                                    // todo
                                    break;
                                case 'button':
                                    // todo fix numbering! je moet ook de nesting in rekening brengen
                                    $counter = 0;
                                    for ($k = 0; $k < sizeof($_SESSION['frontend']->pages[$i]->components); $k++) {
                                        if ($_SESSION['frontend']->pages[$i]->components[$k]->type == 'button') $counter++;
                                    }
                                    $parentPath = '';
                                    if(isset($_SESSION['frontend']->pages[$i]->components[$j]->componentPath)){
                                       $parentPath =  $_SESSION['frontend']->pages[$i]->components[$j]->componentPath;
                                    }
                                    $_SESSION['frontend']->pages[$i]->components[$j]->ci->contentInjection[substr($item,strpos($item,'_')+1)]=
                                        new \components\Button\Button($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id,
                                        $_SESSION['frontend']->pages[$i]->name . '_button' . '_component_' . $counter, 'button',
                                            $parentPath.'_'.$_SESSION['frontend']->pages[$i]->components[$j]->id);
                                    break;
                                case 'card':
                                    // todo
                                    break;
                            }
                        }
                    }
                    //echo '<pre>'.print_r($_SESSION['frontend']->pages[$i]->components[$j]->ci->contentInjection, true).'</pre>';
                }
            }
        }
    }
} else if (isset($_POST['delete-ci']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // todo fix: bij elke nieuwe create wordt voor elk prop de oude waarde overschreven
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                // todo dit is niet per se de geselecteerde component
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected && (int)$_POST['delete-ci'] === $_SESSION['frontend']->pages[$i]->components[$j]->id) {
                    $keys = array_keys($_POST);
                    for ($k=0;$k<sizeof($keys);$k++) {
                        if (str_contains($keys[$k], 'ci_')) {
                            //echo 'key = '.$keys[$k].' vs '.substr($keys[$k],strpos($keys[$k],'_')+1);
                            $_SESSION['frontend']->pages[$i]->components[$j]->ci->contentInjection[substr($keys[$k],strpos($keys[$k],'_')+1)]=NULL;
                            break;
                        }
                    }
                    break;
                }
                    //echo '<pre>'.print_r($_SESSION['frontend']->pages[$i]->components[$j]->ci->contentInjection, true).'</pre>';
                }
            break;
            }
        }
} else if (isset($_POST['mapping']) && isset($_POST['component']) && isset($_POST['page']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // je kan nu een mapping hebben voor meerdere acties => todo: de mapping koppelen aan de juiste actie
    // todo de format is: mapping is een array, de keys zijn actionNames ,
    //  is telkens een array met als keys de property names van de component
    //  en als waarde de naam van het gemapte veld of NULL indien er geen mapping is
    //  indien er geen enkele mapping is, is de array gewoon leeg
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->id === (int)$_POST['page']) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->id === (int)$_POST['component']) {
                    for ($k = 0; $k < sizeof($_SESSION['actions']); $k++) {
                        if (isset($_POST['action']) && $_POST['action'] === $_SESSION['actions'][$k]->name) {
                            $props = $_SESSION['frontend']->pages[$i]->components[$j]->getAttributes();
                            $_SESSION['frontend']->pages[$i]->components[$j]->mapping[$_POST['action']] = [];
                            $fieldNames = $_SESSION['actions'][$k]->getFullQualifiedFieldNames();
                            foreach ($props as $prop) {
                                $val = NULL;
                                for ($l = 0; $l < sizeof($fieldNames); $l++) {
                                    if (isset($_POST[$fieldNames[$l]]) && $_POST[$fieldNames[$l]] === $prop) {
                                        $val = $fieldNames[$l];
                                        break;
                                    }
                                }
                                $_SESSION['frontend']->pages[$i]->components[$j]->mapping[$_POST['action']][$prop] = $val;
                            }
                            break;
                        }
                        //echo '<pre>'.print_r($_SESSION['frontend']->pages[$i]->components[$j]->mapping, true).'</pre>';
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
} else if (isset($_POST['add-effect']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    // deze component moet aangepast worden
                    if (isset($_POST['trigger-name']) && isset($_POST['action-name']) && isset($_POST['component-id'])) {
                        $target = (int)$_POST['component-id'];
                        for ($k = 0; $k < sizeof($_SESSION['actions']); $k++) {
                            if ($_SESSION['actions'][$k]->name === $_POST['action-name']) {
                                for ($l = 0; $l < sizeof($_SESSION['frontend']->pages); $l++) {
                                    for ($m = 0; $m < sizeof($_SESSION['frontend']->pages[$l]->components); $m++) {
                                        if ($_SESSION['frontend']->pages[$l]->components[$m]->id === $target) {
                                            $_SESSION['frontend']->effects[] = new Effect(
                                                $_SESSION['effectCounter']++,
                                                $_SESSION['frontend']->pages[$i]->components[$j],
                                                $_POST['trigger-name'],
                                                $_SESSION['actions'][$k],
                                                $_SESSION['frontend']->pages[$l]->components[$m]);
                                            $_SESSION['frontend']->pages[$l]->components[$m]->mapping[$_POST['action-name']] = [];
                                            break;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                    break;
                }
            }
            break;
        }
    }
} else if (isset($_POST['remove-effect']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($k = 0; $k < sizeof($_SESSION['frontend']->effects); $k++) {
        if (isset($_POST['effect-id'])) {
            $id = (int)$_POST['effect-id'];
            if ($_SESSION['frontend']->effects[$k]->id === $id) {
                for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
                    for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                        if ($_SESSION['frontend']->pages[$i]->components[$j]->id === $_SESSION['frontend']->effects[$k]->source->id) {
                            $_SESSION['frontend']->pages[$i]->components[$j]->removeAction($_SESSION['frontend']->effects[$k]->action->name);
                            $_SESSION['frontend']->removeEffect($id);
                            break;
                        }
                    }
                }
                break;
            }
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
} else if (isset($_POST['button-general-properties']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    if (isset($_POST['text'])) {
                        $_SESSION['frontend']->pages[$i]->components[$j]->label = $_POST['text'];
                    }
                    if (isset($_POST['disabled'])) {
                        $_SESSION['frontend']->pages[$i]->components[$j]->disabled = (bool)$_POST['disabled'];
                    }
                    if (isset($_POST['icon']) || isset($_POST['position'])) {
                        $_SESSION['frontend']->pages[$i]->components[$j]->setIcon($_POST['icon'], $_POST['position']);
                    }
                    //echo '<pre>'.print_r($_SESSION['frontend']->pages[$i]->components[$j], true).'</pre>';
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
                    // todo fix: het probleem is dat op het moment dit wordt aangemaakt een bepaalde trigger voor een bepaalde
                    //      pagina mogelijks nog moet worden gemaakt, dwz dat een menubar component op dat moment moet aangemaakt worden
                    //      hetgeen een onhandige koppeling tussen componenten creëert al is er dan inderdaad een directe relatie
                    if ($_SESSION['frontend']->pages[$i]->main) {
                        $menuItems = [];
                        for ($j = 0; $j < sizeof($_SESSION['concepts']); $j++) {
                            // per concept ga je indien er een getAllActie voor bestaat een default menu item krijgen
                            for ($k = 0; $k < sizeof($_SESSION['actions']); $k++) {
                                if ($_SESSION['actions'][$k]->type === 'Get_all' && $_SESSION['actions'][$k]->concept === $_SESSION['concepts'][$j]->name) {
                                    for ($l = 0; $l < sizeof($_SESSION['frontend']->pages); $l++) {
                                        $names = [];
                                        for ($m = 0; $m < sizeof($_SESSION['frontend']->effects); $m++) {
                                            for ($n = 0; $n < sizeof($_SESSION['frontend']->pages[$l]->components); $n++) {
                                                if ($_SESSION['frontend']->pages[$l]->components[$n]->id === $_SESSION['frontend']->effects[$m]->source->id &&
                                                    $_SESSION['frontend']->effects[$m]->trigger === \Enums\PageTriggerType::OnPageLoad &&
                                                    $_SESSION['frontend']->effects[$m]->action->name === $_SESSION['actions'][$k]->name) {
                                                    $names[] = $_SESSION['frontend']->effects[$m]->action->name;
                                                }
                                            }
                                        }
                                        echo $_SESSION['frontend']->pages[$l]->name;
                                        //echo '<pre>'.print_r($names, true).'</pre>';
                                        if (sizeof($names) === 1) {
                                            $menuItems[] = new \components\Menubar\MenuItem($_SESSION['concepts'][$j]->name . 's',
                                                $_SESSION['frontend']->pages[$l]->id
                                                , sizeof($menuItems) + 1);
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        $comp = new \components\Menubar\Menubar($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id,
                            $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter,
                            $_POST['add-component'], NULL,$menuItems
                        );
                    } else {
                        $comp = new \components\Menubar\Menubar($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id, $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter,
                            $_POST['add-component']);
                    }
                    break;
                case 'table':
                    break;
                case 'button':
                    $comp = new \components\Button\Button($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id,
                        $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter, $_POST['add-component']);
                    break;
                case 'card':
                    try {
                        $comp = new \components\Card\Card($_SESSION['componentCounter']++, $_SESSION['frontend']->pages[$i]->id, $_SESSION['frontend']->pages[$i]->name . '_' . $_POST['add-component'] . '_component_' . $counter,
                            $_POST['add-component']);
                    } catch (Exception $e) {
                    }
                    break;
                default:
                    throw new Exception('not implemented');
            }
            $_SESSION['frontend']->pages[$i]->addComponent($comp);
            break;
        }
    }
    // todo maak dat je een pagina kan toevoegen en verwijderen, nu enkel aanpassen
    // todo maak dat je componenten een andere volgorde kan geven zodat je ze niet helemaal moet verwijderen en opnieuw bouwen
} else if (isset($_POST['generate-backend']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    generateBackend($_SESSION['concepts'], $_SESSION['actions'], $_SESSION['pathToRootOfServer']);
} else if (!isset($_POST['generate-frontend'])) {
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
    <link rel="stylesheet" href="configurations.css">
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

    label {
        font-weight: bold;
    }

    th, td {
        padding: 8px;
        border: 1px solid black;
    }

    .screen {
        border: 1px solid #161667;
        margin: 4px;
    }
</style>
<body style="background: #785a7a">
<!---->
<div id="actions" class="screen" style="float:left; min-width: 200px;">
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
<div class="screen" id="action-detail" style="float:left; min-width: 500px;min-height:400px;">
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
<div class="screen" id="pages" style="float:left; min-width: 200px;">
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
<div class="screen" id="page-detail"
     style="float:left; min-width: 500px;min-height:400px;padding: 0 8px">
    <?php
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        showPage($_SESSION['frontend']->pages[$i], $_SESSION['actions'], $implementedTypesOfComponents);
    }
    ?>
</div>
<div class="screen" id="component-detail"
     style="float:left; min-width: 700px;min-height:400px;padding: 0 8px">
    <?php
    for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
        if ($_SESSION['frontend']->pages[$i]->selected) {
            for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                    showComponent($_SESSION['frontend']->pages[$i]->components[$j], $_SESSION['frontend']->pages, $_SESSION['actions'],$implementedTypesOfComponents);
                    break;
                }
            }
            break;
        }
    }
    ?>
</div>
<div class="screen" id="effects"
     style="float:left; min-width: 700px;min-height:400px;padding: 0 8px">
    <h1>Effects</h1>
    <label>Source component: </label><span><?php
        $sourceFound = false;
        for ($i = 0; $i < sizeof($_SESSION['frontend']->pages); $i++) {
            if ($_SESSION['frontend']->pages[$i]->selected) {
                for ($j = 0; $j < sizeof($_SESSION['frontend']->pages[$i]->components); $j++) {
                    if ($_SESSION['frontend']->pages[$i]->components[$j]->selected) {
                        echo $_SESSION['frontend']->pages[$i]->components[$j]->name;
                        $sourceFound = true;
                        break;
                    }
                }
            }
        }
        if (!$sourceFound) {
            echo 'Select a component on the page view to link with a trigger.';
        }
        ?></span>
    <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
        <label>trigger</label><select name="trigger-name" <?php if (!$sourceFound) echo 'disabled'; ?>>
            <option>--select trigger--</option>
            <?php
            $triggers = array_column(\Enums\TriggerType::cases(), 'name');
            $pageTriggers = array_column(\Enums\PageTriggerType::cases(), 'name');
            $triggers = array_merge($triggers, $pageTriggers);
            foreach ($triggers as $t) {
                echo '<option value="' . $t . '">' . $t . '</option>';
            }
            ?>
        </select><label>action</label>
        <select name="action-name" <?php if (!$sourceFound) echo 'disabled'; ?>>
            <option>--select action--</option>
            <?php $actions = array_column($_SESSION['actions'], 'name');
            foreach ($actions as $a) {
                echo '<option value="' . $a . '">' . $a . '</option>';
            }
            ?>
        </select>
        <label>target</label>
        <select name="component-id" <?php if (!$sourceFound) echo 'disabled'; ?>>
            <option>--select component--</option>
            <?php
            $components = [];
            foreach ($_SESSION['frontend']->pages as $p) {
                $components = array_merge($components, $p->components);
            }
            foreach ($components as $ct) {
                echo '<option value="' . $ct->id . '">' . $ct->name . '</option>';
            }
            ?>
        </select>
        <button type="submit" name="add-effect" <?php if (!$sourceFound) echo 'disabled'; ?>>add effect</button>
    </form>
    <table>
        <thead>
        <tr>
            <td>Trigger</td>
            <td>Source Component</td>
            <td>Action</td>
            <td>Target Component</td>
            <td></td>
        </tr>
        </thead>
        <?php // effects: is dit general of specific, beiden: een component beschikt over bepaalde triggers, maar altijd wel één, en je moet die dus per component tonen
        // voorlopig enkel triggers die elke component heeft
        foreach ($_SESSION['frontend']->effects as $e) {
            for ($i = 0; $i < sizeof($components); $i++) {
                if ($components[$i]->id === $e->target->id) {
                    for ($j = 0; $j < sizeof($components); $j++) {
                        if ($components[$j]->id === $e->source->id) {
                            echo "<tr><td>" . $e->trigger->name . "</td><td>" . $components[$j]->name . "</td><td>" . $e->action->name . "</td><td>" . $components[$i]->name . "</td><td>
                <form action='" . $_SERVER['PHP_SELF'] . "' method='post'><input type='hidden' name='effect-id' value='" . $e->id . "'><button type='submit' name='remove-effect'>
                    remove effect
                </button></form>
            </td></tr>";
                            break;
                        }
                    }
                    break;
                }
            }
        }
        ?></table>
</div>
<div style="clear:left;float:none; margin-top: 4px;text-align: center">
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="generate-backend">
        <button type="submit">Generate backend</button>
    </form>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="generate-frontend">
        <button type="submit">Generate frontend</button>
    </form>
</div>
</body>
</html>
