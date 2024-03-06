<?php
function printField(Field $f,bool $included,bool $last){
    // todo aanpassen UI zodat dit duidelijker is
    $printedField = "\n".$f->name.': ';
    if(!$f->hasSubfields()||!(($included && $f->checked)||(!$included&&!$f->checked))) {
        if(($included && $f->checked)||(!$included&&!$f->checked)){
            $printedField.='true';
        } else{
            $printedField.='false';
        }
        if($last){
            $printedField.="\n";
        } else{
            $printedField.=",";
        }
        return $printedField;
    }
    $printedField.='{'."\n";
    $printedField.=printSubFields($f->subfields);
    $printedField.="\n".'}';
    if($last){
        $printedField.="\n";
    } else{
        $printedField.=",";
    }
    return $printedField;
}
function printSubFields(SubFieldSet $sfs){
    $printedSubFields = '';
    for ($i=0;$i<sizeof($sfs->fields);$i++){
        if($i+1==sizeof($sfs->fields)){
            $printedSubFields.=printField($sfs->fields[$i],$sfs->inclusivity,true);
        } else{
           $printedSubFields.=printField($sfs->fields[$i],$sfs->inclusivity,false);
        }
    }
    return $printedSubFields;
}
function generate($concepts, $actions, $path): bool
{
    if ($success = touch($path . '/app.ts')) {
        $fp = fopen($path . '/app.ts', 'w');
        $fileAsStr = file_get_contents('./app.txt');
        $app1 = strstr($fileAsStr, '***add route imports***', true);
        $app3 = substr(strstr($fileAsStr, '***use routes***'), strlen('***use routes***'));
        $app2 = substr($fileAsStr, strlen($app1) + strlen('***add route imports***'),
            strlen($fileAsStr) - strlen($app3) - strlen('***use routes***') - strlen($app1) - strlen('***add route imports***'));
        fwrite($fp, $app1, strlen($app1));
        global $imports;
        $imports = "\n";
        for ($i = 0; $i < sizeof($_SESSION['concepts']); $i++) {
            global $imports;
            $imports .= 'import {router as ' . $_SESSION['concepts'][$i]->name . '} from \'./routes/' . $_SESSION['concepts'][$i]->name . '\'' . "\n";
        }
        fwrite($fp, $imports, strlen($imports));
        fwrite($fp, $app2, strlen($app2));
        global $routes;
        $routes = "\n";
        for ($i = 0; $i < sizeof($_SESSION['concepts']); $i++) {
            global $routes;
            $routes .= 'app.use(\'/' . $_SESSION['concepts'][$i]->name . '\', ' . $_SESSION['concepts'][$i]->name . ')' . "\n";
        }
        fwrite($fp, $routes, strlen($routes));
        fwrite($fp, $app3, strlen($app3));
        fclose($fp);
        if (!file_exists($path . '/routes')) {
            if ($success = mkdir($path . '/routes')) {
                for ($i = 0; $i < sizeof($_SESSION['concepts']); $i++) {
                    if (touch($path . '/routes/' . $_SESSION['concepts'][$i]->name . '.ts')) {
                        if ($fp = fopen($path . '/routes/' . $_SESSION['concepts'][$i]->name . '.ts', 'ab')) {
                            $fileAsStr = file_get_contents('./route.txt');
                            $p1 = strstr($fileAsStr, '***route handlers***', true) . "\n";
                            $p2 = "\n" . trim(substr(strstr($fileAsStr, '***route handlers***'),
                                    strlen('***route handlers***')));
                            fwrite($fp, $p1, strlen($p1));
                            for ($j = 0; $j < sizeof($actions); $j++) {
                                if (str_contains($actions[$j]->name, $_SESSION['concepts'][$i]->name)) {
                                    $api1 = 'router.' . $actions[$j]->verb . '(\''
                                        . '/' . $_SESSION['concepts'][$i]->name . 's\', async (req:any,res:any,next:any)=>{' . "\n\t";
                                    $api2 = "\n" . '}});' . "\n";
                                    fwrite($fp, $api1, strlen($api1));
                                    $fields='';
                                    // dit blokje kan een aparte functie worden
                                    // op te roepen voor elk concept blok
                                    // en dit soms op de true/false plaats
                                    for ($k=0;$k<sizeof($actions[$j]->fieldset->fields);$k++){
                                        // fieldname
                                        if($k+1==sizeof($actions[$j]->fieldset->fields)){
                                            $fields.=printField($actions[$j]->fieldset->fields[$k],$actions[$j]->fieldset->inclusivity,true);
                                        } else{
                                            $fields.=printField($actions[$j]->fieldset->fields[$k],$actions[$j]->fieldset->inclusivity,false);
                                        }
/*                                        $fields.=$actions[$j]->fieldset->fields[$k]->name.':';
                                        if(($actions[$j]->fieldset->inclusivity&&$actions[$j]->fieldset->fields[$k]->checked)
                                        ||(!$actions[$j]->fieldset->inclusivity&&!$actions[$j]->fieldset->fields[$k]->checked)){
                                            $fields.='true,'."\n";
                                        } else{
                                            $fields.='false,'."\n";
                                        }*/
                                    }
                                    $body = 'try { const result = await e.select(e.' . ucfirst($_SESSION['concepts'][$i]->name)
                                        . ', () => ({' . "\t" .
//                   ...e.' . ucfirst($_SESSION['concepts'][$i]) . '[\'*\']
                           $fields.
               '})).run(client);' . "\n" . '        if (result) {
            res.status(200).send(result)
        } else res.status(400)' . "\t" . '} catch(err){' . "\t" . '
           res.status(500).json({' . "\t\t" . '
               error: err
           })';
                                    fwrite($fp, $body, strlen($body));
                                    fwrite($fp, $api2, strlen($api2));
                                }
                            }
                            fwrite($fp, $p2, strlen($p2));
                            fclose($fp);
                        }

                        /* 5. per file print de verschillende API's
            * het algo voor elke API is als volgt:
            * isoleer alle acties voor dit concept,
                         * per actie schrijf je
            * /body/
            * });
            * Voor de /body/:
            *
            * VOORLOPIG is dit voldoende, we gaan ook nog niet de verb in kwestie in detail beoordelen is voor volgende stap
            *

            }*/
                    }

                }
            }
        }
    }

    /*
    * 2. todo maak een aparte SESSIONS variabele met enkel de concepten erin
    *    todo pas de implemented actions array aan zodat er het subpath in voorkomt en het verb
    *    todo pas actions aan zodat concept, actie en subpath erin voorkomen
    * 3. maak een subdirectory per concept in $concepts
     * */

    /*
     * per concept: maak de nodige routefiles aan
     * de verschillende bestanden per concept zijn als volgt te bepalen:
     * enkel bij een zuiver subconcept moet de file van de routes voor dat subconcept in dezelfde folder als het
     * hoofdconcept, edoch dan moeten we een apart algo schrijven die bepaald wat de zuivere subconcepten zijn en
     * dat is er nu wat over zodus =>
     * 4. één route file per concept BASTA! alle namen zijn ENKELVOUD!
     */


    /* lees app.text
    * vervang ***blabla*** met de eigenlijke routes zoals per file gecreëerd
    *
    * */
    return $success;
}