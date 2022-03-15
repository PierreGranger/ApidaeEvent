<?php
	
	date_default_timezone_set('Europe/Paris') ;

	require realpath(dirname(__FILE__)).'/vendor/autoload.php' ;
	require realpath(dirname(__FILE__)).'/config.inc.php' ;
	
	/** Pour tout usage classique d'ApidaeEvent */
	//$apidaeEvent = new \PierreGranger\ApidaeEvent($configApidaeEvent) ;

	/** Pour l'usage d'Apidae seulement sur event.apidae-tourisme.com */
	$apidaeEvent = new \PierreGranger\ApidaeEventMM($configApidaeEvent) ;

	$apidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeEvent) ;

	if ( isset($flush) && $flush === true ) var_dump($apidaeEvent->flush()) ;

	/**
	 * Require pour l'utilisation en multi membre
	 */
	if ( @$configApidaeEvent['projet_ecriture_multimembre'] == true )
		require_once(realpath(dirname(__FILE__)).'/requires.multimembre.inc.php') ;