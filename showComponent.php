<?php

use components\Card\Card;
use components\Component;

function showComponent($c, $pages, $actions,$implementedTypesOfComponents){
// todo datamapping is niet opportuun voor elk type component

    $part='<h1>Component configuration of '.$c->type.'</h1>';
    $part.='<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><label>name</label><input value="'.$c->name.'" name="component-name"><button type="submit" name="component-edited">save</button></form>';

    $part.='<h2>Specific configuration</h2>';
    if($c->type==='menubar'){
        $part.='<h3>Menu Items</h3>';
        $part.='<form  action="' . $_SERVER['PHP_SELF'] . '" method="post"><label>item</label><input name="item-name">
            <label>page</label><select name="page"><option>select page</option>';
        foreach ($pages as $p){
            $part.='<option value="'.$p->id.'">'.$p->name.'</option>';
        }
        $part.='</select>
            <button type="submit" name="add-item">add item</button></form>';
        $part.='<ul style="margin:0">';
        foreach ($c->menuItems as $menuItem){
            $part.='<li><form action="' . $_SERVER['PHP_SELF'] . '" method="post" style="display: inline"><input type="hidden" name="remove-item" value="'
                .$menuItem->name.'"><button type="submit" name="remove">remove</button></form>
<form action="' . $_SERVER['PHP_SELF'] . '" method="post" style="display: inline"><label>Number</label>
<input type="number" name="menu-item-number" value="'.$menuItem->number.'">
<label>Item</label>
<input type="text" name="menu-item-name" value="'.$menuItem->name.'">
<label>page</label><select name="page"><option>select page</option>';
            foreach ($pages as $p){
                if($p->id===$menuItem->page){
                    $part.='<option value="'.$p->id.'" selected>'.$p->name.'</option>';
                } else{
                    $part.='<option value="'.$p->id.'">'.$p->name.'</option>';}
            }
            $part.='</select><input type="hidden" name="edit-item" value="'.$menuItem->number.'"><button type="submit" name="save-item">save</button></form></li>';
        }
        $part.='</ul>';
    }
    if($c->type==='card'){
        $part.='<form id="save-ci" action="' . $_SERVER['PHP_SELF'] . '" method="post"></form>';
        $part.='<form id="delete-ci" action="' . $_SERVER['PHP_SELF'] . '" method="post"></form>';
        $part.='<h3>Content Injection<button type="submit" form="save-ci">save</button></h3>';
/*        echo '<pre>'.print_r($c->ci, true).'</pre>';
        echo 'true='.is_array($c->ci);*/

        $part.='<ul style="margin:0;width: 740px">';
        foreach ($c->ci->contentInjection as $ciPropName=>$component){
            $part.='<li style="display:block;overflow:auto"><span style="display:block;float:left; width: 150px">'.$ciPropName.'</span>';
            $compstr = '';
            if($component instanceof Component){
                $compstr.='<label style="display:block;float:left; margin-right:16px;">'.$component->name.'</label>
<input type="hidden" name="delete-ci" value="'.$c->id.'" form="delete-ci">
<button style="display:block;float:left;" name="delete-ci_'.$ciPropName.'" type="submit" form="delete-ci">delete</button>
<button style="display:block;float:left;" type="button">edit</button>';
            }
            $part.='<select form="save-ci" style="display:block;float:left; margin-right:16px;" name="ci_'.$ciPropName.'"><option value="">--select a component type--</option>';
            foreach ($implementedTypesOfComponents as $type){
                $part.='<option value="'.$type.'">'.$type.'</option>';
            }
            $part.='</select>'.$compstr.'</li>';
        }
        $part.='</ul><input type="hidden" name="save-ci" value="'.$c->id.'" form="save-ci">';
    }
    if($c->type==='button'){
        $part.='<h3>General properties</h3>';
        $part.='<form  action="' . $_SERVER['PHP_SELF'] . '" method="post" style="display: inline">
            <label>text</label>';
        isset($c->label) ? $part.='<input name="text" value="'.$c->label.'">':$part.='<input name="text">';
        $part.=' <label>disabled</label>';
        isset($c->disabled) && $c->disabled===true ?
            $part.='<input type="radio" name="disabled" value="1" checked>yes<input type="radio" name="disabled" value="0">no':
            $part.='<input type="radio" name="disabled" value="1">yes<input type="radio" name="disabled" value="0" checked>no';
        $part.=' <label>icon</label>
            <select name="icon"><option>--select icon--</option>';
        $icons = array_column(\Enums\IconType::cases(), 'name');
        foreach ($icons as $icon){
            isset($c->icon->icon) && $c->icon->icon->name===$icon ? $part.='<option value="'.$icon.'" selected>'.$icon.'</option>':
                $part.='<option value="'.$icon.'">'.$icon.'</option>';
        }
        $part.='</select>
            <select name="position"><option>--select icon position--</option>';
        $iconPosTypes = array_column(\Enums\IconPositionType::cases(), 'name');
        foreach ($iconPosTypes as $pos){
            isset($c->icon->position) && $c->icon->position->name===$pos ? $part.='<option value="'.$pos.'" selected>'.$pos.'</option>':
                $part.='<option value="'.$pos.'">'.$pos.'</option>';
        }
        $part.='</select>
            <button type="submit" name="button-general-properties">save</button></form>';
    }
    $part.='<h2>General configuration</h2>';

    if($c instanceof Card) {
        // todo voeg hier op termijn ook de button aan toe
        $part .= '<h3>Data Mapping</h3>';
        $props = $c->getAttributes();
        if(sizeof($c->mapping)===0){
            // todo bij het toevoegen van een effect qua target een comp aanpassen wat betreft mapping
            $part .= '<span>No action linked with this component</span>';
        } else{
            foreach(array_keys($c->mapping) as $actionName){
                for ($i=0;$i<sizeof($actions);$i++){
                    if($actions[$i]->name===$actionName){
                        if(sizeof($c->mapping[$actionName])>0){
                            // todo kan er ook een NULL waarde zijn ipv een array
                            // er zijn zoveel ingaves als er propernames zijn voor de overeenkomstige component
                            $part .= '<label>Action: </label><input readonly value="'.$actionName.'">';
                            $part .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><ul style="width: 440px">';
                            foreach ($actions[$i]->getFullQualifiedFieldNames() as $fieldName) {
                                $part .= '<li style="display:block;overflow:auto"><span style="display:block;float:left;">' . $fieldName . '</span>
<select style="display:block;float:right;" name="'. $fieldName . '"><option>-- Selecteer een render property --</option>';
                                foreach ($c->mapping[$actionName] as $key => $value) {
                                    if (isset($value) && $fieldName === $value) {
                                        $part .= '<option selected value="' . $key . '">' . $key . '</option>';
                                    } else {
                                        $part .= '<option value="' . $key . '">' . $key . '</option>';
                                    }
                                }
                                $part .= '</select></li>';
                            }
                        }else {
                            // er is nog geen mapping voor de overeenkomstige action , doch selchts een lege array => todo of is de waarde dan NULL?
                            $fqfn = $actions[$i]->getFullQualifiedFieldNames();
                            $part .= '<label>Action: </label><input readonly value="'.$actionName.'">';
                            $part .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><ul style="width: 440px">';
                            foreach ($fqfn as $fieldName) {
                                $part .= '<li style="display:block;overflow:auto"><span style="display:block;float:left;">' . $fieldName . '</span>
<select style="display:block;float:right;" name="'. $fieldName . '"><option>-- Selecteer een render property --</option>';
                                for ($i = 0; $i < sizeof($props); $i++) {
                                    if (str_contains($fieldName, '_')) {
                                        $strEx = explode('_', $fieldName);
                                        if ($strEx[sizeof($strEx) - 1] === $props[$i]) {
                                            $part .= '<option selected value="' . $props[$i] . '">' . $props[$i] . '</option>';
                                        } else {
                                            $part .= '<option value="' . $props[$i] . '">' . $props[$i] . '</option>';
                                        }
                                    } else if ($fieldName === $props[$i]) {
                                        $part .= '<option selected value="' . $props[$i] . '">' . $props[$i] . '</option>';
                                    } else {
                                        $part .= '<option value="' . $props[$i] . '">' . $props[$i] . '</option>';
                                    }
                                }
                                $part .= '</select></li>';
                            }
                        }
                        $part .= '</ul>
<input type="hidden" name="component" value="' . $c->id . '"><input type="hidden" name="page" value="' . $c->pageId . '">
<input type="hidden" name="action" value="' . $actionName . '">
<button type="submit" name="mapping">Save</button></form>';
                        break;
                        // de gebruiker zal per actie moeten bewaren in de backend gewoon bepalen over welke actie het gaat
                    }
                }
            }
        }
    }
    echo $part;
}
