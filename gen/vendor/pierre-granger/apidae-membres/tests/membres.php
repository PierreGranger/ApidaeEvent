<?php

    require(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require(realpath(dirname(__FILE__)).'/../config.inc.php') ;
    require(realpath(dirname(__FILE__)).'/runtimes.inc.php') ;
    
    $ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;
    
    $id_membre = 1147 ;

    echo '<script src="//code.jquery.com/jquery-1.12.2.min.js"></script>'.PHP_EOL ;
    echo '<script src="./jquery.beautify-json.js"></script>'.PHP_EOL ;
    echo '<link rel="stylesheet" type="text/css" href="./beautify-json.css">'.PHP_EOL ;
    
    echo '<h1>tests getMembre</h1>' ;

    ruStart(__FILE__) ;
    try {
    
        echo '<h2>getMembreById('.$id_membre.')</h2>'.PHP_EOL ;
        $membreById = $ApidaeMembres->getMembreById($id_membre,Array('PROJETS')) ;
        echo '<pre>$ApidaeMembres->getMembreById('.$id_membre.',Array(PROJETS)) ;</pre>' ;
        echo '<pre data-type="json">'.json_encode($membreById).'</pre>'.PHP_EOL ;
    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }
    ruShow(__FILE__) ;

?><script>jQuery('pre[data-type="json"]').beautifyJSON();</script>
    