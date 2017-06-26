<?php
	
	function pre($var)
	{
		echo '<pre>' ;
			print_r($var) ;
		echo '</pre>' ;
	}

	// http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-decriture/cas-particulier-des-multimedias
	// Méthode empruntée de https://github.com/guzzle/guzzle/blob/3a0787217e6c0246b457e637ddd33332efea1d2a/src/Guzzle/Http/Message/PostFile.php#L90
	function getCurlValue($filePath, $contentType, $fileName)
	{
	// Disponible seulement en PHP >= 5.5
	if (function_exists('curl_file_create')) {
	    return curl_file_create($filePath, $contentType, $fileName);
	}

	// Version compatible PHP < 5.5
	$value = "@{$filePath};filename=" . $fileName;
	if ($contentType) {
	    $value .= ';type=' . $contentType;
	}

	return $value;
	}

	function sans_accent_utf8($chaine)
	{
		$accent = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ" ;
		$noaccent= "AAAAAAACEEEEIIIIDNOOOOOOUUUUYbsaaaaaaaceeeeiiiidnoooooouuuyyby" ;
		return str_replace(str_split_unicode($accent),str_split_unicode($noaccent),$chaine) ;
	}
	