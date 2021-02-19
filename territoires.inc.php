<?php

    require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;

    $territoires = Array() ;
    foreach ( $configApidaeEvent['membres'] as $m ) {
        $territoires[$m['id_territoire']] = null ;
    }

    $territoires_appels = array_chunk(array_keys($territoires),200) ;

    try {
        foreach ( $territoires_appels as $terrs )
        {
            $query = Array(
                'apiKey' => $configApidaeEvent['projet_consultation_apiKey'],
                'projetId' => $configApidaeEvent['projet_consultation_projetId'],
                'identifiants' => $terrs,
                'count' => 200,
                'responseFields' => Array('nom','id','localisation')
            ) ;
            $url_api = $ApidaeEvent->url_api().'/api/v002/recherche/list-objets-touristiques/?query='.json_encode($query) ;
            $tmp = file_get_contents($url_api) ;
            $json = json_decode($tmp) ;
            if ( json_last_error() != JSON_ERROR_NONE ) throw new \Exception('not json') ;
            if ( ! isset($json->objetsTouristiques) ) throw new \Exception('objetsTouristiques not found') ;
            foreach ( $json->objetsTouristiques as $obt )
            {
                $obt->perimetre = Array() ;
                foreach ( $obt->localisation->perimetreGeographique as $com )
                    $obt->perimetre[$com->code] = $com ;
                $territoires[$obt->id] = $obt ;
            }
        }
    }
    catch ( \Exception $e ) {
        echo $m['id_territoire'] ;
        echo $e->getMessage() ;
    }