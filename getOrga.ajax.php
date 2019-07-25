<?php

	require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;

	$url = 'http://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques/' ;

	$query = Array(
		'apiKey' => $configApidaeEvent['projet_consultation_apiKey'],
		'projetId' => $configApidaeEvent['projet_consultation_projetId'],
		'searchFields' => 'NOM',
		'searchQuery' => $_GET['search'],
		'responseFields' => Array('id','nom','localisation.adresse.codePostal','localisation.adresse.commune.nom')
	) ;

	$criteresQuery = Array(
		'type:STRUCTURE'
	) ;

	$query['criteresQuery'] = implode(' ',$criteresQuery) ;

	$goto_clear = $url.'?query='.json_encode($query) ;
	$goto = $url.'?query='.urlencode(json_encode($query)) ;
	
	if ( @$_GET['debug'] )
	{
		echo '<a href="'.$goto.'" target="_blank">'.$goto_clear.'</a>' ;
		echo '<hr />' ;
		echo '<pre>' ;
	}	
	
	echo file_get_contents($goto) ;