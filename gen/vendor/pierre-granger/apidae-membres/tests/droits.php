<?php

    require(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require(realpath(dirname(__FILE__)).'/../config.inc.php') ;
    require(realpath(dirname(__FILE__)).'/runtimes.inc.php') ;

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    $ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;

    echo '<script src="//code.jquery.com/jquery-1.12.2.min.js"></script>'.PHP_EOL ;
    echo '<script src="./jquery.beautify-json.js"></script>'.PHP_EOL ;
    echo '<link rel="stylesheet" type="text/css" href="./beautify-json.css">'.PHP_EOL ;
    
    // If you have SSO, you should have an representative array of user looking like :
    $utilisateurApidaeDemo = Array(
        'id' => 14015,
        'firstName' => 'First Name',
        'lastName' => 'Last Name',
        'email' => 'p.granger@allier-tourisme.net',
        'type' => 'MEMBRE',
        'membre' => Array(
            'id' => 1147,
            'nom' => 'Allier Tourisme',
            'type' => Array(
                'elementReferenceType' => 'MembreSitraType',
                'id' => 233,
                'libelleFr' => 'Contributeur Généraliste'
            )
        )
    ) ;

    echo '<h2>user from SSO</h2><pre>'.print_r($utilisateurApidaeDemo,true).'</pre>' ;

    ruStart('membres') ;
    $droits = Array('membres'=>Array(1,1147,2)) ;
    echo '<h2>droits('.json_encode($droits).') ?</h2>' ;
    echo '<p>Should return true as membre(1147) is in ('.implode(',',$droits['membres']).')</p>' ;
    echo var_dump($ApidaeMembres->droits($utilisateurApidaeDemo,$droits)) ;
    ruShow('membres') ;

    ruStart('filleuls') ;
    $droits = Array('filleuls'=>1147) ;
    echo '<h2>droits('.json_encode($droits).') ?</h2>' ;
    echo '<p>Should return false as membre(1147) of user 14015 is not sponsored by himself</p>' ;
    echo var_dump($ApidaeMembres->droits($utilisateurApidaeDemo,$droits)) ;
    ruShow('filleuls') ;

    ruStart('permissions') ;
    $droits = Array('permissions'=>Array('ADMIN_DEPARTEMENTAL')) ;
    echo '<h2>droits('.json_encode($droits).') ?</h2>' ;
    echo '<p>Should return true as user 14015 is an admin</p>' ;
    echo var_dump($ApidaeMembres->droits($utilisateurApidaeDemo,$droits)) ;
    ruShow('permissions') ;
