<?php

use components\Button\Button;

function showComponent($c, $pages){
// todo wijzig code zodat er onderscheid wordt gemaakt tussen de verschillend componenten waar nodig
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
    $part.='<h3>Data Mapping</h3>';
    $props = $c->getAttributes();
    //echo '<pre>'.print_r(isset($c->actionLink), true).'</pre>';
        if(sizeof($c->mapping)>0 && $c->actionLink){
            // todo aanpassen, is niet langer met index maar met keys
            $part.='<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><ul style="width: 440px">';
            foreach ($c->actionLink->getFullQualifiedFieldNames() as $fieldName){
                // todo het probleem hier is dat het weer omgekeerd moet:
                //      je moet mappen op FQFNs
$part.='<li style="display:block;overflow:auto"><span style="display:block;float:left;">'.$fieldName.'</span>
<select style="display:block;float:right;" name="'.$fieldName.'"><option>-- Selecteer een render property --</option>';
                foreach ($c->mapping as $key=>$value){
                    if(isset($value) && $fieldName===$value){
                        $part.='<option selected value="'.$key.'">'.$key.'</option>';
                    } else{
                        $part.='<option value="'.$key.'">'.$key.'</option>';
                    }
                }
                $part.='</select></li>';
            }
            $part.='</ul>
<input type="hidden" name="component" value="'.$c->id.'"><input type="hidden" name="page" value="'.$c->pageId.'"><button type="submit" name="mapping">Save</button></form>';
        } else if(isset($c->actionLink)){
            $fqfn = $c->actionLink->getFullQualifiedFieldNames();
            $part.='<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><ul style="width: 440px">';
            foreach ($fqfn as $fieldName){
                $part.='<li style="display:block;overflow:auto"><span style="display:block;float:left;">'.$fieldName.'</span>
<select style="display:block;float:right;" name="'.$fieldName.'"><option>-- Selecteer een render property --</option>';
                for ($i=0;$i<sizeof($props);$i++){
                    if(str_contains($fieldName,'_')){
                        $strEx = explode('_',$fieldName);
                        if($strEx[sizeof($strEx)-1]===$props[$i]){
                            $part.='<option selected value="'.$props[$i].'">'.$props[$i].'</option>';
                        } else {
                            $part.='<option value="'.$props[$i].'">'.$props[$i].'</option>';
                        }
                    } else if($fieldName===$props[$i]){
                        $part.='<option selected value="'.$props[$i].'">'.$props[$i].'</option>';
                    } else{
                        $part.='<option value="'.$props[$i].'">'.$props[$i].'</option>';
                    }
                }
                $part.='</select></li>';
            }
            $part.='</ul>
<input type="hidden" name="component" value="'.$c->id.'"><input type="hidden" name="page" value="'.$c->pageId.'"><button type="submit" name="mapping">Save</button></form>';
        } else{
            $part.='<span>No action linked with this component</span>';
        }
    echo $part;
}
