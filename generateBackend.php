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
function generateBackend($concepts, $actions, $path): bool
{
    // todo aanpassen naar nieuwe acties
    /*
     * router.patch('remove/from/watchlist/:accountId/:contentId', async (req: any, res: any, next: any) => {
    try {
        const content = e.select(e.Content, (m) => ({
            filter_single: {id: req.params.contentId}
        }))
        e.update(e.Account, (acc) => ({
            filter_single: {id: req.params.accountId},
            set: {
                watchlist: {"-=": content}
            }

        })).run(client)
        // todo return waarde kiezen in speciaal geval zoals dit neen => later toe te voegen
                const result = await e.select(e.Content,()=>({
                            id: true,
                            title: true,
                            actors: {name: true},
                            // calculated properties if requested so
                            filter_single: {id: req.contentId}
                        })).run(client)
        const result = await e.select(e.Account, () => (
                // zal gebeuren op basis van config gebruiker
                {
                    filter_single: {id: req.params.accountId},
                    username: true,
                    watchlist: true,
                }
            )
        ).run(client);
         if (result) {
                    res.status(200).send(result)
                } else res.status(400)
            } catch (err) {
                res.status(500).json({
                            error: err
                        })
            }
        });
 */
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
                        // per concept de nodige routes
                        if ($fp = fopen($path . '/routes/' . $_SESSION['concepts'][$i]->name . '.ts', 'ab')) {
                            $fileAsStr = file_get_contents('./route.txt');
                            $p1 = strstr($fileAsStr, '***route handlers***', true) . "\n";
                            $p2 = "\n" . trim(substr(strstr($fileAsStr, '***route handlers***'),
                                    strlen('***route handlers***')));
                            fwrite($fp, $p1, strlen($p1));
                            for ($j = 0; $j < sizeof($actions); $j++) {
                                // elke actie moet een endpoint krijgen indien de actie activated staat
                                if (str_contains($actions[$j]->name, $_SESSION['concepts'][$i]->name)) {
                                    // voor het gemak gaan we ervan uit dat de naam van een actie altijd de naam van het concept bevat
                                    // todo acties voorzien van een link met het desbetreffende concept namelijk via een id
                                    //      anders kan er concept verwarring zijn doordat twee concepten het één bestaat als voorvoegsel bij het andere
                                    //      bv product en productmanager
                                    function getFields(FieldSet $fs){
                                        $fields='';
                                        // dit blokje kan een aparte functie worden
                                        // op te roepen voor elk concept blok
                                        // en dit soms op de true/false plaats
                                        for ($k=0;$k<sizeof($fs->fields);$k++){
                                            // fieldname
                                            if($k+1==sizeof($fs->fields)){
                                                $fields.=printField($fs->fields[$k],$fs->inclusivity,true);
                                            } else{
                                                $fields.=printField($fs->fields[$k],$fs->inclusivity,false);
                                            }
                                            /*                                        $fields.=$actions[$j]->fieldset->fields[$k]->name.':';
                                                                                    if(($actions[$j]->fieldset->inclusivity&&$actions[$j]->fieldset->fields[$k]->checked)
                                                                                    ||(!$actions[$j]->fieldset->inclusivity&&!$actions[$j]->fieldset->fields[$k]->checked)){
                                                                                        $fields.='true,'."\n";
                                                                                    } else{
                                                                                        $fields.='false,'."\n";
                                                                                    }*/
                                        }
                                        return $fields;
                                    }
                                    if($actions[$j]->type==='Get_all'){
                                        $api1 = 'router.' . $actions[$j]->verb . '(\''
                                            // todo getActionUrl method of bewaar die onmiddellijk in de actie
                                            . '/' . $_SESSION['concepts'][$i]->name . 's\', async (req:any,res:any,next:any)=>{' . "\n\t";
                                        $api2 = "\n" . '}});' . "\n";
                                        fwrite($fp, $api1, strlen($api1));
                                        $fields = getFields($actions[$j]->fieldset);
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
                                    } else if($actions[$j]->type==='Remove_one'||$actions[$j]->type==='Add_one'){
                                        $api1 = 'router.' . $actions[$j]->verb . '(\''
                                            . '/';
                                        $replaceWith = 'add';
                                        if($actions[$j]->type==='Remove_one'){
                                            $replaceWith .= 'from';
                                        }
                                        $api1.=str_replace($_SESSION['concepts'][$i]->name,$replaceWith,$actions[$j]->clientUrl);
                                        // $urlPart.
                                        //$concept->name. => dees moet er tussenuit en from of to moet er dan tussen
                                        //'/'.$set->fields[$i]->name.'/:'.$concept->name.'Id/:'.$set->fields[$i]->type.'Id'];
                                        $api1 .=  '\', async (req:any,res:any,next:any)=>{' . "\n\t";
                                        $api2 = "\n" . '}});' . "\n";
                                        fwrite($fp, $api1, strlen($api1));
                                        $fields = getFields($actions[$j]->fieldset);
                                        $body = 'try {'."\n";
                                        // todo
                                        fwrite($fp, $body, strlen($body));
                                        fwrite($fp, $api2, strlen($api2));
                                    }
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