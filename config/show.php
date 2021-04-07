<?php

    require_once(realpath(dirname(__FILE__)).'/auth.inc.php') ;
    require_once(realpath(dirname(__FILE__)).'/../territoires.inc.php') ;

    $fails = 0 ;

?><!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
        <script
            src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
            integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
            crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="./jsoneditor.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    </head>
    <body>

    <div class="alert alert-danger">
        <h1>Abonné ?</h1>
        <h2>19/02/2021</h2>
        <p>Attention : Pour l'instant, la colonne "Attention" avec la mention "Non abonné !" n'est pas fiable : en attente du traitement d'un ticket chez Smile :<br />
        <a href="https://apidae-tourisme.zendesk.com/agent/tickets/16451">https://apidae-tourisme.zendesk.com/agent/tickets/16451</a></p>
    </div>

    <style>
        thead, thead th {
            position:sticky ;
            top:0 ;
        }
    </style>
        <table class="table table-striped" style="font-size:.7em;">
            <thead class="table-dark">
                <th>Ordre</th>
                <th>ID_membre + Nom</th>
                <th>Site</th>
                <th>Territoire (+ Codes INSEE)</th>
                <th>Parrain</th>
                <th>Mails</th>
                <th>Communes</th>
                <th>Attention</th>
            </thead>
            <tbody>
                <?php
                    $tri = 0 ;

                    $already = Array() ;

                    foreach ( $configApidaeEvent['membres'] as $m ) {
                        $ids_membres[] = $m['id_membre'] ;
                        echo '<tr>' ;
                            echo '<th>'.@$tri++.'</th>';
                            echo '<th><span class="badge bg-secondary">'.$m['id_membre'].'</span> '.$m['nom'].'</th>' ;
                            echo '<td>'.$m['site'].'</td>' ;
                            echo '<td>' ;
                                if ( isset($territoires[$m['id_territoire']]) )
                                {
                                    $json = $territoires[$m['id_territoire']] ;
                                    echo '<span class="badge bg-secondary">'.$json->id.'</span> '.$json->nom->libelleFr ;
                                    echo ' ('.sizeof($json->localisation->perimetreGeographique).')' ;
                                    if ( $json->type != 'TERRITOIRE' ) echo '<div class="alert alert-danger">'.$json->type.'</div>' ;
                                    echo '<div style="font-size:.5em;">' ;
                                        $tmp = Array() ;
                                        foreach ( $json->localisation->perimetreGeographique as $com )
                                        {
                                            $tmp[] = $com->code.'|'.$com->nom ;
                                        }
                                        echo implode(' ',$tmp) ;
                                    echo '</div>' ;
                                }
                                else echo '<div class="alert alert-danger">'.$m['id_territoire'].' Not found</div>' ;
                            echo '</td>' ;
                            echo '<td>'.$m['parrain'].'</td>' ;
                            echo '<td>' ;
                                if ( ! isset($m['mail']) ) echo '<div class="alert alert-danger">Pas de mail ?</div>' ;
                                else echo @implode(', ',@$m['mail']) ;
                            echo '</td>' ;
                            echo '<td>' ;
                                if ( isset($m['insee_communes']) ) echo implode(', ',$m['insee_communes']) ;
                            echo '</td>' ;
                            echo '<td>' ;
                                if ( $fails < 5 )
                                {
                                    try {
                                        $ApidaeMembres->start('getMembreById('.$m['id_membre'].')') ;
                                        $membre = $ApidaeMembres->getMembreById(
                                            $m['id_membre'],
                                            Array('PROJETS')
                                        ) ;
                                        if ( isset($membre['projets']) )
                                        {
                                            $trouve = false ;
                                            foreach ( $membre['projets'] as $p )
                                            {
                                                if ( $p['id'] == $configApidaeEvent['projet_ecriture_projetId'] )
                                                {
                                                    $trouve = true ;
                                                    break ;
                                                }
                                            }
                                            if ( ! $trouve ) echo '<span class="badge bg-danger">Pas abonné !</span>' ;

                                        }
                                    } catch ( Exception $e ) {
                                        $fails++ ;
                                    } finally {
                                        $ApidaeMembres->stop('getMembreById('.$m['id_membre'].')') ;
                                    }
                                }
                                if ( in_array($m['id_membre'],$already) )
                                    echo '<span class="badge bg-warning text-dark">Doublon ?</span>' ;
                            echo '</td>' ;
                        echo '</tr>' ;
                        $already[] = $m['id_membre'] ;
                    }

                ?>
            </tbody>
        </table>

        <?php ini_set('display_errors',0) ; $ApidaeMembres->timer() ; ?>

    </body>
</html>