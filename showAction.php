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
        $part.=showConceptBlock($action->fieldset->conceptName,$action->fieldset->inclusivity);
        $part.='<ul>';
        for ($j = 0; $j < sizeof($action->fieldset->fields); $j++) {
            $part .=showField($action->fieldset->fields[$j],$action->fieldset->conceptName);
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
function showConceptBlock(string $conceptName, bool $incl,string $path=NULL){
    if(!$path){
        $part = '<div>'.$conceptName.' <label><input onchange="checkFields(\''.$conceptName.'\')" type="radio" name="fieldsConfig_'.$conceptName.'" value="1"';
        if ($incl) {
            $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields(\''.$conceptName.'\')" type="radio" name="fieldsConfig_'.$conceptName.'" value="0"> Exclude</label></div>';
        } else {
            $part .= '> Include</label>
                    <label><input type="radio" name="fieldsConfig_'.$conceptName.'" value="0" checked> Exclude</label>
            </div>';
        }
    } else{
        $part = '<div>'.$conceptName.' <label><input onchange="checkFields(\''.$path.'\')" type="radio" name="fieldsConfig_'.$path.'" value="1"';
        if ($incl) {
            $part .= ' checked> Include</label>
                    <label><input onchange="uncheckFields(\''.$path.'\')" type="radio" name="fieldsConfig_'.$path.'" value="0"> Exclude</label></div>';
        } else {
            $part .= '> Include</label>
                    <label><input type="radio" name="fieldsConfig_'.$path.'" value="0" checked> Exclude</label>
            </div>';
        }
    }
    return $part;
}
function showField(Field $f,string $conceptPath){
    // todo fix bug with $contentPath: je hebt ergens season_content_person als conceptPath terwijl season daar niets mee vandoen heeft
    $part = '<li><label>' . $f->name . '<input type="checkbox" name="'.$conceptPath.'_'
        . $f->name
        . '_checkbox" value="1"';
    if ($f->checked) {
        $part .= ' checked></label>';
    } else {
        $part .= '></label>';
    }
    if($f->hasSubfields()){
        $part .= showSubFields($f->subfields);
    }
    $part.='</li>';
    return $part;
}
function showSubFields(SubFieldSet $sfs){
    $part=showConceptBlock($sfs->conceptName,$sfs->inclusivity,$sfs->conceptPath);
    $part.='<ul>';
    foreach ($sfs->fields as $subf){
        $part.=showField($subf,$sfs->conceptPath);
    }
    $part.='</ul>';
    return $part;
}
