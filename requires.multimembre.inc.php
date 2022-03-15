<?php
	
	$ext = isset($configApidaeEvent['env']) && $configApidaeEvent['env'] != 'prod' ? $configApidaeEvent['env'] : 'com' ;
	$url_ws_abonnes = 'https://ws.apidae-tourisme.'.$ext ;
