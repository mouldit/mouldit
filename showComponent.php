<?php
function showComponent($c,$pages){
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
    // todo fix bug: als je de naam van een component wijzigt, dan
    if($c->type==='card'){

    }
    $part.='<h2>General configuration</h2>';
    $part.='<h3>Data Mapping</h3>';
    if(isset($c->actionLink)){

    }
    echo $part;
}
