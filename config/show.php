<?php

    require_once(realpath(dirname(__FILE__)).'/auth.inc.php') ;

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
                $territoires[$obt->id] = $obt ;    
        }
    }
    catch ( \Exception $e ) {
        echo $m['id_territoire'] ;
        echo $e->getMessage() ;
    }

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

    <?php if ( false ) { ?>
    <div class="alert alert-danger">
        <h1>Détermination du propriétaire</h1>
        <p>Attention : le territoire n'est demandé qu'à titre indicatif.<br />
        Le propriétaire d'une manifestation sera déterminé par la liste des comunes qui lui sont affectées sur sa fiche membre Apidae.</p>
    </div>
    <?php } ?>
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
                <th>Territoire</th>
                <th>Parrain</th>
                <th>Mails</th>
                <th>Communes</th>
            </thead>
            <tbody>
                <?php
                    $tri = 0 ;
                    foreach ( $configApidaeEvent['membres'] as $m ) {
                        echo '<tr>' ;
                            echo '<th>'.@$tri++.'</th>';
                            echo '<th>'.$m['id_membre'].' '.$m['nom'].'</th>' ;
                            echo '<td>'.$m['site'].'</td>' ;
                            echo '<td>' ;
                                if ( isset($territoires[$m['id_territoire']]) )
                                {
                                    $json = $territoires[$m['id_territoire']] ;
                                    echo $json->id.' '.$json->nom->libelleFr ;
                                    echo ' ('.sizeof($json->localisation->perimetreGeographique).')' ;
                                    if ( $json->type != 'TERRITOIRE' ) echo '<div class="alert alert-danger">'.$json->type.'</div>' ;
                                }
                                else echo '<div class="alert alert-danger">'.$m['id_territoire'].' Not found</div>' ;
                            echo '</td>' ;
                            echo '<td>'.$m['parrain'].'</td>' ;
                            echo '<td>' ;
                                echo implode(', ',$m['mail']) ;
                            echo '</td>' ;
                            echo '<td>' ;
                                if ( isset($m['insee_communes']) ) echo implode(', ',$m['insee_communes']) ;
                            echo '</td>' ;
                        echo '</tr>' ;
                    }
                ?>
            </tbody>
        </table>

    </body>
</html>