<?php
function generate($concepts, $actions, $path): bool
{
    $success = true;
    /* STRATEGIE
     * 1. maak een routes directory
     */
    if (!file_exists($path . '/routes')) {
        if (mkdir($path . '/routes')) {
            for ($i = 0; $i < sizeof($_SESSION['concepts']); $i++) {
                if (touch($path . '/routes/' . $_SESSION['concepts'][$i] . '.js')) {
                    if($fp = fopen($path . '/routes/' . $_SESSION['concepts'][$i] . '.js','ab')){
                        $fileAsStr = file_get_contents('./route.txt');
                        $p1 = strstr($fileAsStr,'***replace this with actual route handlers***',true)."\n";
                        $p2 = "\n".trim(substr(strstr($fileAsStr,'***replace this with actual route handlers***'),
                                strlen('***replace this with actual route handlers***')));
                        fwrite($fp,$p1,strlen($p1));
                        for ($j = 0; $j < sizeof($actions); $j++) {
                            if (str_contains($actions[$j]->name, $_SESSION['concepts'][$i])) {
                                $api1 = 'router.'.$actions[$j]->verb.'(\''.$_SESSION['concepts'][$i]
                                    .'/'.$_SESSION['concepts'][$i].'s\', async (req,res,next)=>{'."\n";
                                $api2 = "\n".'});'."\n";
                                fwrite($fp,$api1,strlen($api1));
                                // todo write body
                                fwrite($fp,$api2,strlen($api2));
                            }
                        }
                        fwrite($fp,$p2,strlen($p2));
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
        try {
           return e.select(e./Concept met hoofdletter/, () => ({
                   /prop1/: true/false afhankelijk van de config include/exclude,
                   /prop2/: true,
                   ...
               }))
               .run(client);
        } catch(err){
           res.status(500).json({
               error: err
           })
        }*/
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