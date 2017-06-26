<?php

	require_once(realpath(dirname(__FILE__)).'/config.inc.php') ;

	$mysqli = new mysqli('localhost', $mysqli_user, $mysqli_password, $mysqli_db) ;

	if ($mysqli->connect_error) {
    die('Erreur de connexion (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);

    $mysqli->set_charset('utf8') ;
}
