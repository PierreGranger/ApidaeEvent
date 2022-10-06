<?php

    /**
     * Ce fichier doit définir une variable $infos_proprietaire à partir de $_GET['membre']
     */

    /**
     * @var array $infos_proprietaire
     *  $infos_proprietaire = [
     *      'mail_membre' => (string|array) Adresses mails qui recevront les alertes
     *      'structure_validatrice' => (string) Nom de la structure, affiché à l'utilisateur qui a suggéré la manif
     *      'url_structure_validatrice' => (string) Idem
     *      'proprietaireId' => (int) Identifiant Apidae du membre qui sera propriétaire de la manif
     * ]
     */

	/**
	 * @var array $ko
	 * Stocke les erreurs qui seront affichées à l'utilisateur.
	 * Si $ko n'est pas vide, l'enregistrement ne se fera pas.
	 */

	/**
	 * Fail over : si on a trouvé aucun propriétaire dans la liste 
	 */
	$infos_proprietaire = Array(
		'mail_membre' => $configApidaeEvent['mail_admin'],
		'structure_validatrice' => $configApidaeEvent['nom_membre'],
		'url_structure_validatrice' => $configApidaeEvent['url_membre'],
		'proprietaireId' => $configApidaeEvent['membre']
	) ;

	/**
	 * On va chercher les infos dans la config (même si on connait déjà l'id membre / proprietaireId)
	 * parce qu'on a besoin des mails et d'autres infos (nom de la structure, url...)
	 */
	if ( isset($configApidaeEvent['membres']) ) {
		// https://stackoverflow.com/a/14224895/2846837
		$membreTrouve = current(
			array_filter($configApidaeEvent['membres'], function($m) {
				return $m['id_membre'] == $_GET['membre'] ;
			})
		) ;
	} else die('Impossible d\'utiliser l\'affectation forcée sans config[membres]') ;
	
	if ( ! $membreTrouve ) die('Aucum membre '.$_GET['membre'].' trouvé en configuration') ;
	else {
		$infos_proprietaire['proprietaireId'] = $membreTrouve['id_membre'] ;
		$infos_proprietaire['mail_membre'] = @$membreTrouve['mail'] ;
		$infos_proprietaire['structure_validatrice'] = $membreTrouve['nom'] ;
		$infos_proprietaire['url_structure_validatrice'] = $membreTrouve['site'] ;
	}