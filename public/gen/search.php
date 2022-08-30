<?php

require_once(realpath(dirname(__FILE__)) . '/../requires.inc.php');
require_once(realpath(dirname(__FILE__)) . '/../vendor/autoload.php');
require_once(realpath(dirname(__FILE__)) . '/vendor/autoload.php');

$ApidaeSso = new \PierreGranger\ApidaeSso($configApidaeSso, $_SESSION['ApidaeSso']);

header('Content-Type: application/json');

if (!isset($_GET['q'])) die('{"error":0,"lib":"Précisez votre recherche"}');
if (strlen(trim($_GET['q'])) < 3) die('{"error":1,"lib":"précisez votre recherche"}');
//if ( ! preg_match('#^[a-z0-9-_\'"]{5,50}$#Ui',$_GET['q']) ) die('{"error":2,"Caractères de recherche interdit (seulement a-z0-9-_\'", 5 à 50 caractères)..."}') ;

$query = array(
    'projetId' => $configApidaeEvent['projet_consultation_projetId'],
    'apiKey' => $configApidaeEvent['projet_consultation_apiKey'],
    'criteresQuery' => 'type:TERRITOIRE',
    'searchQuery' => urlencode($_GET['q']),
    'searchFields' => 'NOM',
    'responseFields' => array('nom', 'id', 'localisation.perimetreGeographique', 'informationsTerritoire.territoireType')
);
$url = 'https://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques?query=' . json_encode($query);

$c = json_decode(file_get_contents($url), true);

if (json_last_error() != JSON_ERROR_NONE) die('{"error",3,"retour Apidae incorrect"}');

if (!isset($c['query'])) die('{"error",3,"retour Apidae incorrect"}');

if ($c['numFound'] == 0) die('{"error",4,"Aucun résultat"}');

if ($c['numFound'] >  100) die('{"error",5,"Trop de résultats : précisez votre recherche"}');

unset($c['query']);
foreach ($c['objetsTouristiques'] as $k => $v) {
    $c['objetsTouristiques'][$k]['nom'] = $v['nom']['libelleFr'];
    unset($c['type']);

    // Retrait de la liste de communes
    if (isset($c['objetsTouristiques'][$k]['localisation']['perimetreGeographique']) && sizeof($c['objetsTouristiques'][$k]['localisation']['perimetreGeographique']) > 0) {
        $c['objetsTouristiques'][$k]['nbCommunes'] = sizeof($c['objetsTouristiques'][$k]['localisation']['perimetreGeographique']);
        unset($c['objetsTouristiques'][$k]['localisation']);
    }

    // territoireType
    if (isset($c['objetsTouristiques'][$k]['informationsTerritoire']['territoireType']) && sizeof($c['objetsTouristiques'][$k]['informationsTerritoire']['territoireType']) > 0) {
        $c['objetsTouristiques'][$k]['territoireType'] = $c['objetsTouristiques'][$k]['informationsTerritoire']['territoireType']['libelleFr'];
        unset($c['objetsTouristiques'][$k]['informationsTerritoire']);
    }
}

echo json_encode($c['objetsTouristiques']);
