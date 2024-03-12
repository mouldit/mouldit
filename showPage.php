<?php
function showPage(Page $page,$actions){
    $part = '';
    if ($page->selected) {
        $part .=
            '<h2 style="margin: 0 0 8px 0;">Details of page: ' . $page->name . '</h2>
             <form  style="width:400px" action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <div style="overflow: auto;">
                    <label style="display:block; margin-bottom:8px; float:left">name </label>
                    <input style="display:block; float:right; min-width: 170px" type="text" name="name" value="'.$page->name.'">
                    <label style="display:block; margin-bottom:8px; clear:left;float:left">url</label>
                    <input style="display:block; clear:right; float:right; min-width: 170px" type="text" name="url" value="'.$page->url.'">
                    <label style="display:block; margin-bottom:8px; clear:left;float:left">on page load</label>
                    <select name="action" style="display:block; clear:right;float:right; min-width: 178px">';
        foreach ($actions as $a){
            if($page->action==$a->name){
                $part.='<option selected value="'.$a->name.'">'.$a->name.'</option>';
            } else{
                $part.='<option value="'.$a->name.'">'.$a->name.'</option>';
            }

        }
        $part.='</select>
                 </div>';
        $part .=
            '<div><button type="submit" name="page-edited">save</button></div>
            </form><br>
            <div>
                <form style="float:right;" action="' . $_SERVER['PHP_SELF']. '" method="post">
                    <input type="hidden" name="generate-frontend"><button type="submit">Generate frontend</button>
                </form>
            </div>';
        echo $part;
    }
}
