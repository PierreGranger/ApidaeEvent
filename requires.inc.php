<?php
	
	ini_set('display_errors',1) ;
	error_reporting(E_ALL) ;
	date_default_timezone_set('Europe/Paris') ;

	$autoloader = require realpath(dirname(__FILE__)).'/vendor/autoload.php' ;
	
	require_once(realpath(dirname(__FILE__)).'/config.inc.php') ;

	require_once(realpath(dirname(__FILE__)).'/src/ApidaeEvent.php') ;

	$pma = new \PierreGranger\ApidaeEvent($_config) ;

	if ( isset($_GET['refresh']) )
	{
		$pma->getCommunes(true) ;
		$pma->setTerritoires(true) ;
		$pma->getElementsReference(null,true) ;
	}