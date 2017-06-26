<?php
	
	ini_set('display_errors',1) ;
	error_reporting(E_ALL) ;
	date_default_timezone_set('Europe/Paris') ;

	$modes = Array('CREATION','MODIFICATION','DEMANDE_SUPPRESSION') ;

	require_once(realpath(dirname(__FILE__)).'/config.inc.php') ;
	require_once(realpath(dirname(__FILE__)).'/fonctions.inc.php') ;
	require_once(realpath(dirname(__FILE__)).'/ApidaeEvent.class.php') ;

	$pma = new ApidaeEvent($_config) ;

	if ( isset($_GET['refresh']) )
	{
		$pma->getCommunes(true) ;
		$pma->getTerritoires(true) ;
		$pma->getElementsReference(null,true) ;
	}