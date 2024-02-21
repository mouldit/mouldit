<?php
function generate($concepts,$actions,$path):bool{
    $success=true;
    /* STRATEGIE
     * 1. maak een routes directory
     * 2. todo maak een aparte SESSIONS variabele met enkel de concepten erin
     *    todo pas de implemented actions array aan zodat er het subpath in voorkomt en het verb
     *    todo pas actions aan zodat concept, actie en subpath erin voorkomen
     * 3. maak een subdirectory per concept in $concepts
     * per concept: maak de nodige routefiles aan
     * de verschillende bestanden per concept zijn als volgt te bepalen:
     * enkel bij een zuiver subconcept moet de file van de routes voor dat subconcept in dezelfde folder als het
     * hoofdconcept, edoch dan moeten we een apart algo schrijven die bepaald wat de zuivere subconcepten zijn en
     * dat is er nu wat over zodus =>
     * 4. één route file per concept BASTA! alle namen zijn ENKELVOUD!
     * 5. per file print de verschillende API's
     * het algo voor elke API is als volgt:
     * isoleer alle acties voor dit concept, per actie schrijf je router./verb uit de action/({/subpath uit de action/, async (req,res,next)=>{
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
    }
     * lees app.text
     * vervang ***blabla*** met de eigenlijke routes zoals per file gecreëerd
     *
     * */
    return $success;
}