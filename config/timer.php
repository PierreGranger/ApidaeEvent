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

<?php
    try {

        $apidaeMembres->start('getMembreById()') ;

        $membre = $apidaeMembres->getMembreById(
            1157,
            Array('PROJETS')
        ) ;
        print_r($membre) ;
    } catch ( Exception $e ) {
        $fails++ ;
    } finally {
        $apidaeMembres->stop('getMembreById()') ;
    }

    $apidaeMembres->timer() ;

?>

</body>
</html>