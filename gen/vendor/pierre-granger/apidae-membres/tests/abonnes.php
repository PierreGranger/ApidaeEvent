<?php

    require(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require(realpath(dirname(__FILE__)).'/../config.inc.php') ;
    require(realpath(dirname(__FILE__)).'/runtimes.inc.php') ;

    $configApidaeMembres['debug'] = true ;
    $ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;

    $projet_recherche = 2792 ; // ApidaeEvent (multi-membres)

    echo '<script src="//code.jquery.com/jquery-1.12.2.min.js"></script>'.PHP_EOL ;
    echo '<script src="./jquery.beautify-json.js"></script>'.PHP_EOL ;
    echo '<link rel="stylesheet" type="text/css" href="./beautify-json.css">'.PHP_EOL ;
    
    ruStart('getMembres') ;
    try {

        $responseFields = Array("PROJETS") ;
        $query = Array( 'communeCode'=>"03150", "idProjet" => $projet_recherche ) ;
        $membresCommune = $ApidaeMembres->getMembres($query,$responseFields) ;
        
        echo '<h2>Recherche des membres concernés par la commune '.implode(',',$query).'...</h2>' ;
        echo '<pre>$ApidaeMembres->getMembres('.json_encode($query).','.json_encode($responseFields).') ;</pre>' ;
        $membresAbonnes = Array() ;
        foreach ( $membresCommune as $mc )
        {
            echo '<h3>#'.$mc['id'].' '.$mc['nom'].'</h3>' ;
            if ( isset($mc['projets']) )
            {
                echo '<ul>' ;
                foreach ( $mc['projets'] as $p )
                {
                    if ( $p['id'] == $projet_recherche ) $membresAbonnes[] = $mc ;
                    echo '<li>' ;
                        if ( $p['id'] == $projet_recherche ) echo '<strong>' ;
                        echo '#'.$p['id'].' : '.$p['nom'] ;
                        if ( $p['id'] == $projet_recherche ) echo '</strong>' ;
                    echo '</li>' ;
                }
                echo '</ul>' ;
            }
        }

    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }
    ruShow('getMembres') ;

    echo '<hr />' ;
    echo '<h2>Membres abonnés au projet '.$projet_recherche.' sur la recherche '.json_encode($query).' ('.sizeof($membresAbonnes).')</h2>' ;

    // On a les abonnés : s'il y en a plusieurs sur la commune, on doit chercher le plus petit.
    foreach ( $membresAbonnes as $ma )
    {
        if ( in_array($ma['id'],\PierreGranger\ApidaeMembres::$idCRT) ) continue ; // On ignore volontairement Apidae Tourisme et Aura Tourisme
        echo '<h3>'.$ma['nom'].'</h3>' ;
        echo '<pre data-type="json">'.json_encode($ma).'</pre>'.PHP_EOL ;
    }

    ?><script>jQuery('pre[data-type="json"]').beautifyJSON();</script>