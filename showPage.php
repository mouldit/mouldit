<?php
function showPage(Page $page,$actions,$implementedTypesOfComponents){
    $part = '';
    $compWithAction = null;
    for ($i=0;$i<sizeof($page->components);$i++){
        if(isset($page->components[$i]->actionLink)){
            // todo voorlopig alleen 1 component maar de bedoeling is natuurlijk om er meer te zetten
            $compWithAction = $page->components[$i];
            break;
        }
    }
    if ($page->selected) {
        $part .=
            '<h2 style="margin: 0 0 8px 0;">Details of page: ' . $page->name . '</h2>
             <form  style="width:500px" action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <div style="overflow: auto;">
                    <label style="display:block; margin-bottom:8px; float:left">name </label>
                    <input style="display:block; float:right; min-width: 170px" type="text" name="name" value="'.$page->name.'">
                    <label style="display:block; margin-bottom:8px; clear:left;float:left">url</label>
                    <input style="display:block; clear:right; float:right; min-width: 170px" type="text" name="url" value="'.$page->url.'">';
        $part .=
            '</div><div><button type="submit" name="page-edited">save</button></div>
            </form><br>
            
            <form style="width:500px;overflow:auto" action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <label style="display:block; margin-bottom:8px;clear:left;float:left">components</label>
            <button style="display:block;clear:right;float:right" type="submit" name="add">add</button>
            <select name="add-component" style="display:block; margin-right:8px;float:right; min-width: 178px">';

        foreach ($implementedTypesOfComponents as $c){
            $part.='<option value="'.$c.'">'.$c.'</option>';
        }
$part.='</select>
</form>
<div>';

        if(sizeof($page->components)>0){
            $part.='<ul style="margin:0">';
            for ($i = 0; $i < sizeof($page->components); $i++) {
                $part.= "<li style='overflow:auto'>
                            <span style='float:left'>" . $page->components[$i]->name . "</span> 
                             <form style='float:right' action=\"" . $_SERVER['PHP_SELF'] . "\" method='post'>
                               <input  type='hidden' value='" . $page->components[$i]->id . "' name='component-id'>
                               <button type='submit' name='new-component-selected'>edit</button>
                            </form>
                         </li>";
            }
            $part.='</ul>';
        } else{
        $part.='<span>Added components will be shown here</span>';
    }
    $part.='
</div>';
        echo $part;
    }
}
