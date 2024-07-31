<?php

include(realpath(dirname(__FILE__)).'/../config.inc.php') ;

// https://www.php.net/manual/en/function.file-put-contents.php#84180
function file_force_contents($dir, $contents){
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = '';
    foreach($parts as $part)
        if(!is_dir($dir .= "/$part")) mkdir($dir);
    file_put_contents("$dir/$file", $contents);
}

//https://dev.apidae-tourisme.com/documentation-technique/v2/api-de-diffusion/liste-des-services/#referentiel
$tmp = file_get_contents('https://static.apidae-tourisme.com/filestore/exports-referentiel/elements_reference.json') ;
$decoded = json_decode($tmp) ;
if ( json_last_error() == JSON_ERROR_NONE ) {
    file_force_contents(realpath(dirname(__FILE__)).'/../ressources/elements_reference.json', $tmp) ;

    // Critères interdits
    // https://apidae-tourisme.zendesk.com/agent/tickets/34957
    // 31/07/2024
    $elementReferenceIds = [] ;
    foreach ( $decoded as $er ) {
        if ( in_array($er->elementReferenceType,['FeteEtManifestationType','FeteEtManifestationCategorie']) ) {
            $elementReferenceIds[] = $er->id ;
        }
    }

    $query = [
        'apiKey' => $configApidaeEvent['projet_consultation_apiKey'],
        'projetId' => $configApidaeEvent['projet_consultation_projetId'],
        'elementReferenceIds' => $elementReferenceIds
    ] ;
    $tmp = file_get_contents('https://api.apidae-tourisme.com/api/v002/referentiel/criteres-interdits/?query='.json_encode($query)) ;
    $res = json_decode($tmp,true) ;
    if ( json_last_error() == JSON_ERROR_NONE ) {
        $interdictions = [] ;
        foreach ( $res as $r ) {
            $interdictions[$r['critereId']] = [] ;
            foreach ( $r as $k => $v ) {
                if ( $k != 'critereId' ) {
                    $interdictions[$r['critereId']][$k] = $v ;
                }
            }
        }
        file_force_contents(realpath(dirname(__FILE__)).'/../ressources/elements_reference_interdits.json', json_encode($interdictions, JSON_PRETTY_PRINT)) ;
    }

}