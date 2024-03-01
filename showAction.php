<?php
function showAction(Action $action)
{
    $part = '';
    if ($action->selected) {
        $part .= '<h2 style="margin: 0">Configure backend of action: ' . $action->name . ' 
       </h2>
       <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div><label><input type="radio" name="isActive" value="1"';
        $part .= showActivationState($action->active);
        $part.=showConceptBlock($action->fieldset);
        $part.='<ul>';
        for ($j = 0; $j < sizeof($action->fieldset->fields); $j++) {
            $part .=showField($action->fieldset->fields[$j]);
        }
        $part.='</ul>';
        $part .= '<div><button type="submit" name="action-edited">save</button></div>
</form><br><div><form style="float:right;" action="' . $_SERVER['PHP_SELF']
            . '" method="post"><input type="hidden" name="generate"><button type="submit">Generate</button></form></div>';
        echo $part;
    }
}
function showActivationState(bool $isActive){
    if ($isActive) {
        return ' checked> ON</label>
                    <label><input type="radio" name="isActive" value="0"> OFF</label></div>';
    } else {
        return '> ON</label>
                    <label><input type="radio" name="isActive" value="0" checked> OFF</label>
            </div>';
    }
}
function showConceptBlock(FieldSet $fs,SubFieldSet $sfs=NULL){
    // indien dit null is dan is fs een main field set
    // anders is fs een fieldset van het subfieldset zoals gespecifieerd in de tweede param
    // todo zie dat elk concept block zijn eigen naam heeft
    $part = '<div>'.$fs->conceptName.' <label><input onchange="checkFields()" type="radio" name="fieldsConfig" value="1"';
    if ($fs->inclusivity) {
        $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields()" type="radio" name="fieldsConfig'.getPath($fs,$sfs).'" value="0"> Exclude</label></div>';
    } else {
        $part .= '> Include</label>
                    <label><input type="radio" name="fieldsConfig" value="0" checked> Exclude</label>
            </div>';
    }
    return $part;
}
function getPath(FieldSet $fs,SubFieldSet $sfs=NULL){
    $path='_'.$fs->conceptName;
    if(isset($sfs)){
        if(isset($sfs->parentFieldSet)){
            // dit is het main fieldset
            $path.=getPath($sfs->parentFieldSet->fields,$sfs->parentSubFieldSet);
        } else{
            // het bovenliggende set is ook een subfieldset
            $path.=
        }
    }
    return $path;
}
function showField(Field $f){
    $part = '<li><label>' . $f->name . '<input type="checkbox" name="'
        . $f->name
        . 'Checked" value="1"';
    if ($f->checked) {
        $part .= ' checked></label>';
    } else {
        $part .= '></label>';
    }
    echo '<pre>veld => '.print_r($f, true).'</pre><br>';
    if($f->hasSubfields()){
        // todo dit geeft nu false terug omdat wellicht het veld niet correct aangemaakt wordt
        //      waarom: omdat het parent field gezet moet worden anders werkt het allemaal niet
        $part .= showSubFields($f);
    }
    $part.='</li>';
    return $part;
}
function showSubFields(Field $f){
    $part=showConceptBlock($f->subfields->fields);
    $part.='<ul>';
    foreach ($f->subfields->fields->fields as $sf){
        $part.=showField($sf);
    }
    $part.='</ul>';
    return $part;
}