<?php

    require(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require(realpath(dirname(__FILE__)).'/../config.inc.php') ;
    require(realpath(dirname(__FILE__)).'/runtimes.inc.php') ;

    $ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;
    
    $idParrain = ( isset($utilisateurApidae) ) ? $utilisateurApidae['membre']['id'] : 1147 ; // Allier Tourisme

    ruStart('getFilleuls('.$idParrain.')') ;
    try {
        $filleuls = $ApidaeMembres->getFilleuls($idParrain) ;
    }
    catch ( Exception $e ) {
        print_r($e) ;
        die() ;
    }
    ruShow('getFilleuls('.$idParrain.')') ;

    $lignes = Array() ;
    $mailTo = Array() ;
    $mailToStructure = Array() ;
    $mailJet = Array() ;

    foreach ( $filleuls as $f )
    {
        if ( isset($f['utilisateurs']) )
        {
            foreach ( $f['utilisateurs'] as $u )
            {
                $ligne = Array() ;
                $ligne['StructureId'] = $f['id'] ;
                $ligne['StructureNom'] = $f['nom'] ;
                $ligne['StructureTypeId'] = $f['type']['id'] ;
                $ligne['StructureTypeNom'] = $f['type']['nom'] ;
                foreach ( $u as $k => $v ) $ligne[$k] = $v ;
                $lignes[] = $ligne ;
                $mailTo[] = $u['prenom'].' '.$u['nom'].' <'.$u['eMail'].'>' ;
                $mailToStructure[] = $u['prenom'].' '.$u['nom'].' - '.$f['nom'].' <'.$u['eMail'].'>' ;
                $mailJet[] = $u['prenom']."\t".$u['nom']."\t".$u['eMail'] ;
            }
        }
    }

    echo '<h1>Mes filleuls (#'.$idParrain.' / '.@$utilisateurApidae['membre']['nom'].') ('.sizeof($filleuls).' membres)</h1>' ;

    
    echo '<h2>Pour c/c par mail</h2>' ;
    echo '<textarea style="color:white;background:black;width:100%;height:100px;">' ;
        echo htmlentities(implode(';',$mailTo)) ;
    echo '</textarea>' ;

    echo '<h2>Pour c/c par mail (avec structure)</h2>' ;
    echo '<textarea style="color:white;background:black;width:100%;height:100px;">' ;
        echo htmlentities(implode(';',$mailToStructure)) ;
    echo '</textarea>' ;

    echo '<h2>Pour c/c sur mailjet</h2>' ;
    echo '<textarea style="color:white;background:black;width:100%;height:100px;">' ;
        echo htmlentities(implode("\n",$mailJet)) ;
    echo '</textarea>' ;

    echo '<h2>Pour c/c sur excel</h2>' ;
    echo '<table style="font-family:monospace;" border="1" cellspacing="0" cellpadding="4">' ;
        $head = false ;
        foreach ( $lignes as $l )
        {
            if ( ! $head )
            {
                $head = true ;
                echo '<thead>' ;
                    echo '<tr>' ;
                    foreach ( $l as $k => $c )
                    {
                        echo '<th>'.$k.'</th>' ;
                    }
                    echo '</tr>' ;
                echo '</thead>' ;
                echo '<tbody>' ;
            }
            echo '<tr>' ;
            foreach ( $l as $c )
            {
                echo '<td>'.$c.'</td>' ;
            }
            echo '</tr>' ;
        }
        echo '</tbody>' ;
    echo '</table>' ;
