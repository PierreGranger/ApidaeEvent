<?php
	
	date_default_timezone_set('Europe/Paris') ;

	$autoloader = require realpath(dirname(__FILE__)).'/vendor/autoload.php' ;
	
	require_once(realpath(dirname(__FILE__)).'/config.inc.php') ;
	
	$ApidaeEvent = new \PierreGranger\ApidaeEvent($configApidaeEvent) ;
	$ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeEvent) ;
