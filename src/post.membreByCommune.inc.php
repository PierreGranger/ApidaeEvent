<?php

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
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

	/**
	  * @example php post.infos_proprietaire.inc.php debug '30341|73670|Entremont-le-Vieux|73107'
	  */
	if ( isset($argv[1]) && $argv[1] == 'debug' )
	{
		require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;
	}

	$log = new Logger('membres') ;
	$logPath = realpath(dirname(__FILE__) . '/../ressources/logs/membres.log');
	$logRotate = new RotatingFileHandler($logPath, 7, Logger::DEBUG);
	$log->pushHandler($logRotate) ;

    if ( ! isset($debug) ) $debug = false ;

    if ( isset($argv[1]) && $argv[1] == 'debug' )
    {
        $debug = true ;
        $timer = new ApidaeTimer() ;
        $commune = explode('|','14707|37260|Villeperdue|37278') ;
		//$commune = explode('|','1425|03000|Moulins|03190') ;
		//$commune = explode('|','11754|30129|Redessan|30211') ;
		//$commune = explode('|','11893|30120|Le Vigan|30350') ;
		//$commune = explode('|','4451|13710|Fuveau|13040') ;
		//$commune = explode('|','30427|73120|Courchevel|73227') ;
		//$commune = explode('|','1938|06790|Aspremont|06006') ;
		$commune = explode('|','30341|73670|Entremont-le-Vieux|73107') ;
		if ( isset($argv[2]) && preg_match('#^[a-zA-Z0-9- ]+|[a-zA-Z0-9- ]+|.*|[a-zA-Z0-9- ]+$#',$argv[2]) )
			$commune = explode('|',$argv[2]) ;
    }

	

    if ( ! isset($commune) || ! is_array($commune) )
    {
        die('Missing (array)$commune') ;
    }

	$communeId = $commune[0] ;
	$communeInsee = $commune[3] ;

    function ip_debug($var,$titre=null) {
        global $debug, $log ;
		if ( ! $debug ) {
			$logs = [] ;
			if ( $titre != null ) $logs[] = $titre ;
			if ( is_array($var) ) $logs[] = json_encode($var) ; else $logs[] = $var ;
			$log->info(implode(' : ',$logs)) ;
		} else {
			echo PHP_EOL ;
			if ( $titre != null) echo "********** " . $titre . PHP_EOL ;
			if ( is_array($var) ) echo json_encode($var) ;
			else echo $var ;
			echo PHP_EOL ;
		}
    }

    $time = null ;
    function ip_start($var=null) {
        global $debug, $time ;
        if ( $var != null ) ip_debug($var) ;
        $time = microtime(true) ;
    }
    function ip_stop($var=null) {
        global $debug, $time ;
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

	if ( $configApidaeEvent['projet_ecriture_multimembre'] === true && isset($configApidaeEvent['membres']) )
	{
		/**
		 * Note : on a du cache dessus, si besoin on peut rafraichir via scripts/territoires.php
		 */
		ip_start('Récupération des territoires') ;
		$territoires = $apidaeEvent->getTerritoires() ;
		if ( ! $territoires ) {
			ip_debug(sizeof($territoires),'Récupération des territoires impossible') ;
			$ko[] = 'Récupération des territoires impossible' ;
		}
		ip_debug(sizeof($territoires),'Nombre de territoires') ;
		ip_stop() ;

		ip_debug($commune,'$commune') ;

		/**
		 * On commence par utiliser un webservice dédié à la récupération des abonnés d'un projet,
		 * plutôt que l'API membre qui a des temps de réponse très lents
		 */
		ip_start('Recherche des membres abonnés + concernés par la commune '.$communeInsee.' par Webservice') ;
		$membresCommune = false ;
		if ( isset($url_ws_abonnes) )
		{
			try {
				$query = [
					'projetId' => $configApidaeEvent['projet_ecriture_projetId'],
					'communeId' => $communeId,
					'key' => @$configApidaeEvent['wsProjetAbonnesKey']
				] ;
				$tmp = file_get_contents($url_ws_abonnes.'/v1/sit/projets/abonnes?'.http_build_query($query)) ;
				$values = json_decode($tmp,true) ;
				if ( json_last_error() == JSON_ERROR_NONE && is_array($values) )
				{
					ip_debug('Worked ! '.$tmp) ;
					$membresCommune = [] ;
					foreach ( $values as $id_membre )
						$membresCommune[$id_membre] = $id_membre ;
				}
				else ip_debug('Failed ! Not json :( json_last_error()=' . json_last_error()) ;
			} catch ( Exception $e ) {
				ip_debug('Failed ! '.$e->getMessage()) ;
			}
		}
		ip_stop() ;

        /**
		 * Si le Webservice n'a pas pu renvoyer la liste des membres abonnés au projet :
		 * On récupère la liste des membres qui ont les droits sur la commune concernée.
		 * Malheureusement cet appel est très long et manque de précision (on ne peut pas filtrer les membres abonnés)
         */
		if ( $membresCommune === false && ! is_array($membresCommune) )
		{
			ip_start('Recherche des membres à partir de la commune '.$communeInsee) ;
			$membresCommune = $apidaeEvent->getMembresFromCommuneInsee($communeInsee) ;
			if ( ! $membresCommune ) $ko[] = 'Récupération des membres concernés par cette commune impossible' ;
			ip_debug(sizeof($membresCommune),'sizeof($membresCommune)') ;
			ip_debug(array_keys($membresCommune),'array_keys($membresCommune)') ;
			ip_stop() ;
		}

		$doubleCheck = true ;
		if ( ! isset($territoires) || ! is_array($territoires) ) $doubleCheck = false ;
		
        ip_start('Recherche du membre correspondant dans la config') ;
	
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
				if ( $m['id_membre'] == 1157 ) continue ;
				ip_debug('Membre '.$m['id_membre'].' '.$m['nom'].' concerné par la commune [insee='.$communeInsee.']') ;
				
				ip_debug('Le membre '.$m['id_membre'].' possède-t-il la commune [insee='.$communeInsee.'] dans la config ApidaeEvent / insee_communes ?') ;
				$trouve = false ;
				if ( 
					isset($m['insee_communes']) 
					&& is_array($m['insee_communes']) 
				)
				{
					if ( in_array($communeInsee,$m['insee_communes']) ) {
						ip_debug('Oui : '.$communeInsee.' trouvé dans les insee_communes '.implode(', ',$m['insee_communes']).' !') ;
						$trouve = true ;
					} else {
						ip_debug('Non : '.$communeInsee.' absent de insee_communes '.implode(', ',$m['insee_communes'])) ;
					}
				} else {
					ip_debug('Non : pas de conf insee_communes pour '.$m['id_membre']) ;
				}

				ip_debug('Le membre '.$m['id_membre'].' possède-t-il la commune [insee='.$communeInsee.'] dans la config ApidaeEvent /  territoire ?') ;
				ip_debug('membre[id_territoire]='.$m['id_territoire']) ;

				if ( isset($territoires[$m['id_territoire']]) )
					ip_debug('Périmètre du territoire '.$m['id_territoire'].' (codes Insee) : '.implode(',',array_keys($territoires[$m['id_territoire']]['perimetre']))) ;
				else
					ip_debug('/!\ Le territoire '.$m['id_territoire'].' n\'a pas été trouvé en cache dans $territoires...') ;

				if (
					isset($m['id_territoire']) && isset($territoires) && is_array($territoires)
					&& isset($territoires[$m['id_territoire']])
					&& isset($territoires[$m['id_territoire']]['perimetre'][$communeInsee])
				)
				{
					ip_debug('Oui : [insee='.$communeInsee. '] fait partie du territoire '.$m['id_territoire']) ;
					$trouve = true ;
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
