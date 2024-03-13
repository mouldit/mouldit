<?php
function showComponent($c){
    echo '<pre>'.print_r($c->menuItems, true).'</pre>';
    echo 'component:'.$c->name;
}
