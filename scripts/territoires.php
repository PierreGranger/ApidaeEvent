<?php

    require_once(realpath(dirname(__FILE__)).'/../src/requires.inc.php') ;

    $territoires = $apidaeEvent->getTerritoires(true) ;
    foreach ( $territoires as $id => $communes ) {
        $apidaeEvent->getCommunesByTerritoire($id, true) ;
    }