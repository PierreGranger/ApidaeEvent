<?php

	use PierreGranger\ApidaeEvent;

	date_default_timezone_set('Europe/Paris') ;

	require realpath(dirname(__FILE__)).'/../vendor/autoload.php' ;
	require realpath(dirname(__FILE__)).'/../config.inc.php' ;
	require realpath(dirname(__FILE__)).'/functions.inc.php' ;
	

    // https://stackoverflow.com/a/3770616/2846837
    $lang_detected = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'fr' ;
    
	$locale = ApidaeEvent::DEFAULT_LANGUAGE['locale'] ;
	$lang = ApidaeEvent::DEFAULT_LANGUAGE['lang'] ;

	if ( isset($_GET['locale']) && isset(ApidaeEvent::ACCEPTED_LANGUAGES[$_GET['locale']]) ) {
		$locale = ApidaeEvent::ACCEPTED_LANGUAGES[$_GET['locale']]['locale'] ;
		$lang = $_GET['locale'] ;
	}
	elseif ( isset(ApidaeEvent::ACCEPTED_LANGUAGES[$lang_detected]) ) {
		$locale = ApidaeEvent::ACCEPTED_LANGUAGES[$lang_detected]['locale'] ; 
		$lang = $lang_detected ;
	}
	
    // https://www.php.net/manual/fr/function.gettext.php
    $results = putenv('LC_ALL='.$locale);
    $results = setlocale(LC_ALL, $locale);
	$results = setlocale(LC_MESSAGES, $locale);
    bindtextdomain("event", realpath(dirname(__FILE__))."/../locale");
    textdomain("event");
	bind_textdomain_codeset("event", 'UTF-8');

	/** Pour tout usage classique d'ApidaeEvent */
	//$apidaeEvent = new \PierreGranger\ApidaeEvent($configApidaeEvent) ;

	/** Pour l'usage d'Apidae seulement sur event.apidae-tourisme.com */
	$configApidaeEvent['lang'] = $lang ;
	$apidaeEvent = new \PierreGranger\ApidaeEventMM($configApidaeEvent) ;

	$apidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeEvent) ;

	if ( isset($flush) && $flush === true ) var_dump($apidaeEvent->flush()) ;

	/**
	 * Require pour l'utilisation en multi membre
	 */
	if ( @$configApidaeEvent['projet_ecriture_multimembre'] == true )
		require_once(realpath(dirname(__FILE__)).'/requires.multimembre.inc.php') ;
