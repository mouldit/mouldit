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
        // todo fix
        $part.=showConceptBlock($action->fieldset->conceptName,$action->fieldset->inclusivity);
        $part.='<ul>';
        for ($j = 0; $j < sizeof($action->fieldset->fields); $j++) {
            $part .=showField($action->name,$action->fieldset->fields[$j]);
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
function showConceptBlock(string $actionName,string $conceptName, bool $incl, string $fieldPath=NULL){
    if(!$fieldPath){
        $part = '<div>'.$conceptName.' <label><input onchange="checkFields(\''.$actionName.'_checkbox_'.'\')" 
        type="radio" name="'.$actionName.'_fieldsConfig" value="1"';
        if ($incl) {
            $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields(\''.$actionName.'_checkbox_'.'\')" 
                    type="radio" name="'.$actionName.'_fieldsConfig" value="0"> Exclude</label></div>';
        } else {
            $part .= '> Include</label>
                    <label><input type="radio" name="'.$actionName.'_fieldsConfig" value="0" checked> Exclude</label>
            </div>';
        }
    } else{
        $part = '<div>'.$conceptName.' <label><input onchange="checkFields(\''.$actionName.'_checkbox_'.$fieldPath.'\')" 
        type="radio" name="'.$actionName.'_fieldsConfig_'.$fieldPath.'" value="1"';
        if ($incl) {
            $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields(\''.$actionName.'_checkbox_'.$fieldPath.'\')" 
                    type="radio" name="'.$actionName.'_fieldsConfig_'.$fieldPath.'" value="0"> Exclude</label></div>';
        } else {
            $part .= '> Include</label>
                    <label><input type="radio" name="'.$actionName.'_fieldsConfig_'.$fieldPath.'" value="0" checked> Exclude</label>
            </div>';
        }
    }
    return $part;
}
function showField(string $actionName,Field $f,string $fieldString=NULL){
    if(isset($fieldString)) $fieldString='_'.$fieldString; else $fieldString='';
    $part = '<li><label>' . $f->name . '<input type="checkbox" name="'.$actionName.'_checkbox'.$fieldString.'_'.$f->name.'" value="1"';
    if ($f->checked) {
        $part .= ' checked></label>';
    } else {
        $part .= '></label>';
    }
    if($f->hasSubfields()){
        $part .= showSubFields($actionName,$f->subfields);
    }
    $part.='</li>';
    return $part;
}
function showSubFields($actionName,SubFieldSet $sfs){
    // todo fix
    $part=showConceptBlock($sfs->conceptName,$sfs->inclusivity,$sfs->conceptPath);
    $part.='<ul>';
    foreach ($sfs->fields as $subf){
        $part.=showField($actionName,$subf,$sfs->fieldPath);
    }
    $part.='</ul>';
    return $part;
}
