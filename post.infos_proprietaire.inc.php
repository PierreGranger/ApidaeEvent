<?php

    use PierreGranger\ApidaeTimer ;

    /**
     * Ce fichier doit définir une variable $infos_proprietaire à partir de la commune $commune saisie.
     */

    /**
     * La variable $commune doit être définie avant d'appeler ce fichier
     * 
     * @var array $commune
     * 
     * $commune = [
     *      0 => (int) Identifiant commune Apidae
     *      1 => (int) Code postal
     *      2 => (string) Nom de la commune
     *      3 => (int) Code Insee
     * ]
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

    if ( ! isset($debug) ) $debug = false ;

    if ( isset($argv[1]) && $argv[1] == 'debug' )
    {
        require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;
        $debug = true ;
        $timer = new ApidaeTimer() ;
        //$commune = explode('|','14707|37260|Villeperdue|37278') ;
		//$commune = explode('|','1425|03000|Moulins|03190') ;
		//$commune = explode('|','11754|30129|Redessan|30211') ;
		//$commune = explode('|','11893|30120|Le Vigan|30350') ;
		//$commune = explode('|','4451|13710|Fuveau|13040') ;
		$commune = explode('|','30427|73120|Courchevel|73227') ;
    }

    if ( ! isset($commune) || ! is_array($commune) )
    {
        die('Missing (array)$commune') ;
    }

	$communeInsee = $commune[3] ;

    function ip_debug($var,$titre=null) {
        global $debug ; if ( ! $debug ) return false ;
        echo PHP_EOL ;
        if ( $titre != null) echo "********** " . $titre . PHP_EOL ;
        if ( is_array($var) ) echo json_encode($var) ;
        else echo $var ;
        echo PHP_EOL ;
    }

    $time = null ;
    function ip_start($var=null) {
        global $debug ; if ( ! $debug ) return false ;
        global $time ;
        if ( $var != null ) ip_debug($var) ;
        $time = microtime(true) ;
    }
    function ip_stop($var=null) {
        global $debug ; if ( ! $debug ) return false ;
        global $time ;
        $time = microtime(true) - $time ;
        if ( $var != null ) ip_debug($var) ;
        ip_debug(round($time, 3).'s') ;
    }

	/**
	 * Fail over : si on a trouvé aucun propriétaire dans la liste 
	 */

	$infos_proprietaire = Array(
		'mail_membre' => $configApidaeEvent['mail_admin'],
		'structure_validatrice' => $configApidaeEvent['nom_membre'],
		'url_structure_validatrice' => $configApidaeEvent['url_membre'],
		'proprietaireId' => $configApidaeEvent['membre']
	) ;
	ip_debug($infos_proprietaire,'$infos_proprietaire Fail over (default)'.__LINE__) ;

	/**
	 * Si on souhaite pouvoir écrire sur un membre différent en fonction de la commune saisie,
	 * alors dans la config on a renseigné $configApidaeEvent['membres'].
	 */

	if ( $configApidaeEvent['projet_ecriture_multimembre'] === true )
	{
		/**
		 * Note : on a du cache dessus, si besoin on peut rafraichir via scripts/territoires.php
		 */
		ip_start('Récupération des territoires') ;
		$territoires = $apidaeEvent->getTerritoires() ;
		if ( ! $territoires ) $ko[] = 'Récupération des territoires impossible' ;
		ip_debug(sizeof($territoires),'Nombre de territoires') ;
		ip_stop() ;


		/**
		 * On commence par récupérer la liste des membres abonnés au projet d'écriture et qui ont les droits sur la commune concernée.
		*/
		ip_debug($commune,'$commune') ;

        /**
         * + de 6 secondes
         * @todo : optim cache
         * Pas possible de faire des appels plus simple via responseFields (déjà au mini)
         */
        ip_start('Recherche des membres à partir de la commune '.$communeInsee) ;
		$membresCommune = $apidaeEvent->getMembresFromCommuneInsee($communeInsee) ;
		if ( ! $membresCommune ) $ko[] = 'Récupération des membres concernés par cette commune impossible' ;
		ip_debug(sizeof($membresCommune),'sizeof($membresCommune)') ;
		ip_debug(array_keys($membresCommune),'array_keys($membresCommune)') ;
		
		ip_stop() ;

		$doubleCheck = true ;
		if ( ! isset($territoires) || ! is_array($territoires) ) $doubleCheck = false ;
		
        ip_start('Recherche du membre correspondant dans la config') ;
		if ( isset($configApidaeEvent['membres']) )
		{
			if ( $debug ) $timer->start('loop_membres') ;
			/**
             * Au cas où la commune serait concernée par plusieurs territoires, on parcoure les membres dans l'ordre saisi pour choisir le premier dans la liste.
             * Cette boucle est crade mais elle ne prend que 0.003s, aucun intérêt à l'optimiser.
             */
			foreach ( $configApidaeEvent['membres'] as $m )
			{
				/**
				 * On trouve le premier membre concerné (dont une commune sur Apidae correspond à la commune de la manif)
				 * */
				//ip_debug(isset($membresCommune[$m['id_membre']]),'isset($membresCommune['.$m['id_membre'].']) ? '.$m['nom']) ;
				if ( isset($membresCommune[$m['id_membre']]) )
				{
					ip_debug($m['id_membre'],'membre '.$m['nom'].' concerné (boucle)') ;
					$trouve = true ;
					/**
					 * Ce n'est pas suffisant : il faut aussi s'assurer que dans la config c'était bien le territoire choisi
					 */
					if ( $doubleCheck )
					{
						ip_debug('Double check... $communeInsee = '.$communeInsee) ;
						$trouve = false ;
						if ( 
							isset($m['insee_communes']) 
							&& is_array($m['insee_communes']) 
							&& in_array($communeInsee,$m['insee_communes'])
						)
						{
							// Trouvé dans la liste des communes spécifiée en config !
							ip_debug('trouvé dans les insee_communes !') ;
							$trouve = true ;
						}

						// @array_keys(@$territoires[$m['id_territoire']]['perimetre'])
						ip_debug('Recherche de '.$communeInsee.' dans le territoire '.$m['id_territoire'].' du membre...') ;
						if (
							isset($m['id_territoire']) && isset($territoires) && is_array($territoires)
							&& isset($territoires[$m['id_territoire']])
							&& isset($territoires[$m['id_territoire']]['perimetre'][$communeInsee])
						)
						{
							// Trouvé dans le territoire de la config !
							ip_debug('trouvé dans le territoire !') ;
							$trouve = true ;
						}
					}

					if ( $trouve )
					{
						ip_debug($m,'membre trouvé...') ;
						$infos_proprietaire['proprietaireId'] = $m['id_membre'] ;
						$infos_proprietaire['mail_membre'] = @$m['mail'] ;
						$infos_proprietaire['structure_validatrice'] = $m['nom'] ;
						$infos_proprietaire['url_structure_validatrice'] = $m['site'] ;
						break ;
					}
				}
			}
		}
        ip_stop() ;
	}

	ip_debug($infos_proprietaire,'$infos_proprietaire') ;
	
	if ( $debug && $communeInsee == 37278 )
	{
		$infos_proprietaire['proprietaireId'] = $configApidaeEvent['membre'] ;
		$infos_proprietaire['mail_membre'] = @$configApidaeEvent['mail_admin'] ;
		$infos_proprietaire['structure_validatrice'] = $configApidaeEvent['nom_membre'] ;
		$infos_proprietaire['url_structure_validatrice'] = $configApidaeEvent['url_membre'] ;
		ip_debug($infos_proprietaire,'$infos_proprietaire (force Apidae / debug)') ;
	}
