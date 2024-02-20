<?php

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
}