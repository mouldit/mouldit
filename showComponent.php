<?php
function showComponent($c,$pages){
    $part='<h1>Component configuration of '.$c->type.'</h1>';
    $part.='<form><label>name</label><input value="'.$c->name.'" name="component-name"><button type="submit" name="edit-component">save</button></form>';
    $part.='<h2>Specific configuration</h2>';
    $part.='<h3>Menu Items</h3>';
    $part.='<form><label>item</label><input name="item-name">
            <label>page</label><select name="page"><option>select page</option>';
    foreach ($pages as $p){
        $part.='<option value="'.$p->name.'">'.$p->name.'</option>';
    }

    $part.='</select>
            <button type="submit" name="add-item">add item</button></form>';
    $part.='<ul style="margin:0">';
    foreach ($c->menuItems as $menuItem){

        $part.='<li><form style="display: inline"><input type="hidden" name="remove-item" value="'
            .$menuItem->name.'"><button type="submit" name="remove">remove</button></form>
<form style="display: inline"><label>Number</label>
<input type="number" name="menu-item-number" value="'.$menuItem->number.'">
<label>Item</label>
<input type="text" name="menu-item-name" value="'.$menuItem->name.'">
<label>page</label><select name="page"><option>select page</option>';
        foreach ($pages as $p){
            if($p->name===$menuItem->page){
                $part.='<option value="'.$p->name.'" selected>'.$p->name.'</option>';
            } else{
                $part.='<option value="'.$p->name.'">'.$p->name.'</option>';}
        }

        $part.='</select><input type="hidden" name="edit-item" value="'.$menuItem->number.'"> <button type="submit" name="save-item">save</button></form></li>';


    }
    $part.='</ul>';
    echo $part;
}
