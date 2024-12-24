<?php
	
	require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;

	use PierreGranger\ApidaeException ;
	use PierreGranger\ApidaeTimer ;
	use PierreGranger\ApidaeMembres ;
	
	if ( ! isset($_POST['commune']) ) return ;
	
	$verbose = true ;
	$debug = $configApidaeEvent['debug'] ;
	$erreurs_debug = [] ;

	if ( $debug )
		$timer = new ApidaeTimer() ;

	$commune = explode('|',$_POST['commune']) ;

	$infos_orga = Array() ;

	if ( ! isset($ko) ) $ko = Array() ;

	$apidaeEvent->debug($_POST,'$_POST') ;

	$enquete_er_id = 5865 ;
	if ( @$configApidaeEvent['env'] == 'cooking' ) {
		$enquete_er_id = 3934 ;
	}

	/**
	 * $nosave définit s'il faut envoyer en enregistrement sur Apidae ou non.
	 * Il n'a pas d'impact sur l'envoi des mails
	 */
	$nosave = isset($_POST['nosave']) && $debug ;

	if ( $configApidaeEvent['recaptcha_secret'] != '' && ! $debug )
	{
		$fields = array( 'secret' => $configApidaeEvent['recaptcha_secret'], 'response' => $_POST['g-recaptcha-response'] ) ;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		if ( ($result = curl_exec($ch)) === false )
		{
			$ko['Captcha'] = __('Vérification du captcha impossible',false) ;
		}
		else
		{
			$json_result = json_decode($result) ;
			if ( ! $json_result->success ) $ko['Captcha'] = __('L\'utilisateur n\'a pas coché la case "Je ne suis pas un robot"',false) ;
		}
	}

	if ( isset($_GET['membre']) ) {
		include(realpath(dirname(__FILE__)).'/post.affectationForcee.inc.php') ;
	} else {
		include(realpath(dirname(__FILE__)).'/post.membreByCommune.inc.php') ;
	}

	$root = [] ;
	$fieldlist = [] ;
	
	$root['type'] = 'FETE_ET_MANIFESTATION' ;

	$fieldlist[] = 'nom' ;
	if ( $libelleXy != 'libelleFr' ) {
		$root['nom']['libelleFr'] = $_POST['nom'] ;
	}
	$root['nom'][$libelleXy] = $_POST['nom'] ;

	if ( $_POST['lieu'] != '' ) {
		$fieldlist[] = 'informationsFeteEtManifestation.nomLieu' ;
		$root['informationsFeteEtManifestation']['nomLieu'] = $_POST['lieu'] ;
		$fieldlist[] = 'localisation.adresse.nomDuLieu' ;
		$root['localisation']['adresse']['nomDuLieu'] = $_POST['lieu'] ;
	}

	$fieldlist[] = 'localisation.adresse.adresse1' ;
	$root['localisation']['adresse']['adresse1'] = $_POST['adresse1'] ;

	$fieldlist[] = 'localisation.adresse.adresse2' ;
	$root['localisation']['adresse']['adresse2'] = $_POST['adresse2'] ;

	$fieldlist[] = 'localisation.adresse.adresse3' ;
	$root['localisation']['adresse']['adresse3'] = $_POST['adresse3'] ;

	$root['localisation']['adresse']['codePostal'] = $commune[1] ;
	$fieldlist[] = 'localisation.adresse.codePostal' ;
	
	$root['localisation']['adresse']['commune']['id'] = $commune[0] ;
	$fieldlist[] = 'localisation.adresse.commune' ;

	$root['informationsFeteEtManifestation']['portee']['elementReferenceType'] = 'FeteEtManifestationPortee' ;
	$fieldlist[] = 'informationsFeteEtManifestation.portee' ;
	$root['informationsFeteEtManifestation']['portee']['id'] = (int)$_POST['portee'] ;

	if ( isset($_POST['nbParticipantsAttendu']) && is_numeric($_POST['nbParticipantsAttendu']) )
	{
		$root['informationsFeteEtManifestation']['nbParticipantsAttendu'] = (int)$_POST['nbParticipantsAttendu'] ;
		$fieldlist[] = 'informationsFeteEtManifestation.nbParticipantsAttendu' ;
	}

	if ( isset($_POST['nbVisiteursAttendu']) && is_numeric($_POST['nbVisiteursAttendu']) )
	{
		$root['informationsFeteEtManifestation']['nbVisiteursAttendu'] = (int)$_POST['nbVisiteursAttendu'] ;
		$fieldlist[] = 'informationsFeteEtManifestation.nbVisiteursAttendu' ;
	}

	$periodesOuvertures = Array() ;
	foreach ( $_POST['date'] as $i => $date )
	{
		if (sizeof($date) <= 3) {
			$apidaeEvent->debug($date,'date '.$i.' refusée (<4 champ)') ;
			continue ;
		}
		if (! $apidaeEvent->verifDate($date['debut'])) {
			$apidaeEvent->debug($date,'date '.$i.' refusée ('.$date['debut'].' invalide)') ;
			continue ;
		}

		$date['debut'] = $apidaeEvent->dateUs($date['debut']) ;
		$date['fin'] = $apidaeEvent->dateUs($date['fin']) ;

		$db = new DateTime($date['debut']) ;
		
		$periode = Array() ;
		$periode['identifiantTemporaire'] = ( $i + 1 ) ;
		$periode['dateDebut'] = $date['debut'] ;
		$periode['dateFin'] = $apidaeEvent->verifDate($date['fin']) ? $date['fin'] : $date['debut'] ;
		if ( $apidaeEvent->verifTime($date['hdebut']) ) $periode['horaireOuverture'] = $date['hdebut'].":00" ;
		if ( $apidaeEvent->verifTime($date['hfin']) ) $periode['horaireFermeture'] = $date['hfin'].":00" ;
		$periode['tousLesAns'] = false ;
		$periode['type'] = 'OUVERTURE_TOUS_LES_JOURS' ;
		if ( $date['complementHoraire'] != "" ) $periode['complementHoraire'] = [$libelleXy => trim($date['complementHoraire'])] ;

		if ( isset($date['timePeriods']) ) $periode['timePeriods'] = json_decode($date['timePeriods']) ;

		$periodesOuvertures[] = $periode ;
		
	}
	if ( sizeof ($periodesOuvertures) > 0  )
	{
		$fieldlist[] = 'ouverture.periodesOuvertures' ;
		$root['ouverture']['periodesOuvertures'] = $periodesOuvertures ;
	}
	



	/**
	*	Contacts
	*/
	$contacts = Array() ;
	if ( isset($_POST['contact']) && is_array($_POST['contact']) && sizeof($_POST['contact']) > 0 )
	{
		$mc_trouve = false ;
		foreach ( $_POST['contact'] as $post_contact )
		{
			if ( sizeof(array_filter($post_contact)) == 0 ) continue ;
			$contact = Array() ;

			$contact['referent'] = true ;
			$contact['nom'] = $post_contact['nom'] ;
			$contact['prenom'] = $post_contact['prenom'] ;
			$mcs = Array() ;
			if ( $post_contact['mail'] != '' )
			{
				$mcs[] = Array(
					'type'=>Array('id'=>204,'elementReferenceType' => 'MoyenCommunicationType'),
					'coordonnees' => Array('fr' => $post_contact['mail'])
				) ;
				$mail_orga = filter_var($post_contact['mail'], FILTER_VALIDATE_EMAIL) ;
				if ( $mail_orga != '' ) $infos_orga['mail'] = $mail_orga ;
			}
			if ( $post_contact['telephone'] != '' ) $mcs[] = Array(
				'type'=>Array('id'=>201,'elementReferenceType' => 'MoyenCommunicationType'),
				'coordonnees' => Array('fr' => $post_contact['telephone'])
			) ;
			if ( sizeof($mcs) > 0 )
			{
				$mc_trouve = true ;
				$contact['moyensCommunication'] = $mcs ;
			}
			
			if ( isset($post_contact['fonction']) && $post_contact['fonction'] != '' && $post_contact['fonction'] != 0 )
				$contact['fonction'] = Array('id'=>(int)$post_contact['fonction'],'elementReferenceType' => 'ContactFonction') ;
			
			$contacts[] = $contact ;
		}
		
		if ( isset($_GET['contactObligatoire']) && $_GET['contactObligatoire'] == 1 && ! $mc_trouve )
		{
			$ko['Contact obligatoire'] = __('merci de préciser au moins 1 mail ou 1 numéro de téléphone dans la ligne "contact".',false) ;
		}

	}
	elseif ( isset($_GET['contactObligatoire']) && $_GET['contactObligatoire'] == 1 )
		$ko['Contact obligatoire'] = __('merci de préciser au moins 1 contact',false) ;

	if ( sizeof($contacts) > 0 )
	{
		$fieldlist[] = 'contacts' ;
		$root['contacts'] = $contacts ;
	}



	/**
	 * Moyens de communication
	 */
	
	$mcs = Array() ;
	if ( isset($_POST['mc']) && is_array($_POST['mc']) && sizeof($_POST['mc']) > 0 )
	{
		foreach ( $_POST['mc'] as $post_mc )
		{
			if ( trim($post_mc['coordonnee']) == '' ) continue ;
			$mc = Array('type'=>Array('id'=>(int)$post_mc['type'],'elementReferenceType' => 'MoyenCommunicationType')) ;
			$mc['coordonnees']['fr'] = $post_mc['coordonnee'] ;
			$mc['observation']['libelleFr'] = $post_mc['observations'] ;
			$mcs[] = $mc ;

			/** Récupération des infos orga pour envoi d'un mail de notif */
			if ( ! isset($infos_orga['mail']) && (int)$post_mc['type'] == 204 )
			{
				$mail_orga = filter_var($post_mc['coordonnee'], FILTER_VALIDATE_EMAIL) ;
				if ( $mail_orga != '' ) $infos_orga['mail'] = $mail_orga ;
			}
		}
	}
	if ( sizeof($mcs) > 0 )
	{
		$fieldlist[] = 'informations.moyensCommunication' ;
		$root['informations']['moyensCommunication'] = $mcs ;
	}










	/**
	*	Gestion des types catégories themes
	*/
	if ( isset($_POST['FeteEtManifestationType']) )
	{
		$fieldlist[] = 'informationsFeteEtManifestation.typesManifestation' ;
		$root['informationsFeteEtManifestation']['typesManifestation'] = Array() ;
		if ( is_array($_POST['FeteEtManifestationType']) )
		{
			foreach ( $_POST['FeteEtManifestationType'] as $id )
			{
				if ( ! is_numeric($id) ) continue ;
				$root['informationsFeteEtManifestation']['typesManifestation'][] = Array(
					'elementReferenceType' => 'FeteEtManifestationType',
					'id' => $id
				) ;
			}
		}
		else
		{
			if ( is_numeric($_POST['FeteEtManifestationType']) )
			{
				$root['informationsFeteEtManifestation']['typesManifestation'][] = Array(
					'elementReferenceType' => 'FeteEtManifestationType',
					'id' => $_POST['FeteEtManifestationType']
				) ;
			}
		}
	}
	if ( isset($_POST['FeteEtManifestationCategorie']) )
	{
		$fieldlist[] = 'informationsFeteEtManifestation.categories' ;
		$root['informationsFeteEtManifestation']['categories'] = Array() ;
		foreach ( $_POST['FeteEtManifestationCategorie'] as $id )
		{
			$root['informationsFeteEtManifestation']['categories'][] = Array(
				'elementReferenceType' => 'FeteEtManifestationCategorie',
				'id' => $id
			) ;
		}
	}
	if ( isset($_POST['FeteEtManifestationTheme']) )
	{
		$fieldlist[] = 'informationsFeteEtManifestation.themes' ;
		$root['informationsFeteEtManifestation']['themes'] = Array() ;
		foreach ( $_POST['FeteEtManifestationTheme'] as $id )
		{
			$root['informationsFeteEtManifestation']['themes'][]=Array(
				'elementReferenceType' => 'FeteEtManifestationTheme',
				'id' => $id
			) ;
		}
	}


	/**
	*	Gestion des descriptifs
	*/
	if ( isset($_POST['descriptifCourt']) && trim($_POST['descriptifCourt']) != "" )
	{
		$fieldlist[] = 'presentation.descriptifCourt' ;
		$root['presentation']['descriptifCourt'][$libelleXy] = trim($_POST['descriptifCourt']) ;
	}
	if ( isset($_POST['descriptifDetaille']) && trim($_POST['descriptifDetaille']) != "" )
	{
		$fieldlist[] = 'presentation.descriptifDetaille' ;
		$root['presentation']['descriptifDetaille'][$libelleXy] = trim($_POST['descriptifDetaille']) ;
	}

	/**
	 * Toutou
	 */
	if ( isset($_POST['animauxAcceptes']) ) {
		$fieldlist[] = 'prestations.animauxAcceptes' ;
		$root['prestations']['animauxAcceptes'] = 'ACCEPTES' ;
	}
	if ( isset($_POST['descriptifAnimauxAcceptes']) && trim($_POST['descriptifAnimauxAcceptes']) != "" )
	{
		$fieldlist[] = 'prestations.descriptifAnimauxAcceptes' ;
		$root['prestations']['descriptifAnimauxAcceptes'][$libelleXy] = trim($_POST['descriptifAnimauxAcceptes']) ;
	}

	/**
	 * Descriptifs thématisés
	 */
	$dts = Array() ;
	if ( isset($_POST['descriptifsThematises']) )
	{
		foreach ( $_POST['descriptifsThematises'] as $id => $lib )
		{
			if ( trim($lib) != '' )
			{
				$dts[] = Array(
					'theme' => Array(
						'elementReferenceType' => 'DescriptifTheme',
						'id' => $id
					),
					'description' => Array(
						$libelleXy => trim($lib)
					)
				) ;
			}
		}
	}
	if ( sizeof($dts) > 0 )
	{
		$fieldlist[] = 'presentation.descriptifsThematises' ;
		$root['presentation']['descriptifsThematises'] = $dts ;
	}

	/**
	*	Gestion des tarifs
	*/
	if ( isset($_POST['descriptionTarif_complement_'.$codeLibelibelleXylle]) )
	{
		$fieldlist[] = 'descriptionTarif.complement' ;
		$root['descriptionTarif']['complement'][$libelleXy] = trim($_POST['descriptionTarif_complement_'.$libelleXy]) ;	
	}

	if ( isset($_POST['gratuit']) )
	{
		$fieldlist[] = 'descriptionTarif.indicationTarif' ;
		$root['descriptionTarif']['indicationTarif'] = 'GRATUIT' ;
	}
	
	if ( isset($_POST['tarifs']) && is_array($_POST['tarifs']) )
	{
		$tarifs = Array() ;
		foreach ( $_POST['tarifs'] as $tarif )
		{
			if ( $tarif['mini'] == '' && $tarif['maxi'] == '' && trim($tarif['precisions']) == '' ) continue ;
			
			$t = Array('devise' =>$_POST['devise']) ;
			/* TODO si on veut permettre le choix de la devise tarif par tarif par l'internaute : décommenter ci dessous */
			/* ATTENTION ce critère a l'air d'être ignoré par l'API d'écriture, qui semble se servir uniquement de descriptionTarif.devise */
			// $t = Array('devise' =>$tarif['devise']) ;
			
			$t['minimum'] = $tarif['mini'] ;
			$t['maximum'] = $tarif['maxi'] ;
			if ( isset($tarif['precisions']) ) $t['precisionTarif'][$libelleXy] = $tarif['precisions'] ;
			$t['type'] = Array('elementReferenceType'=>'TarifType','id'=>(int)$tarif['type']) ;
			$tarifs[] = $t ;
		}

		if ( sizeof($tarifs) > 0 )
		{
			$dateMin = new DateTime(date('Y').'-01-01') ;
			$dateMax = new DateTime(date('Y').'-12-31') ;
			if ( isset($periodesOuvertures) )
			{
				$dateMin = null ;
				$dateMax = null ;
				foreach ( $periodesOuvertures as $po )
				{
					$periodeMin = new DateTime($po['dateDebut']) ;
					if ( $dateMin == null || $periodeMin < $dateMin ) $dateMin = $periodeMin ;
					$periodeMax = new DateTime($po['dateFin']) ;
					if ( $dateMax == null || $periodeMax > $dateMax ) $dateMax = $periodeMax ;
				}
			}

			if ( $dateMin == null ) $dateMin = new DateTime(date('Y').'-01-01') ;
			if ( $dateMax == null ) $dateMax = new DateTime(date('Y').'-12-31') ;

			$fieldlist[] = 'descriptionTarif.periodes' ;
			$root['descriptionTarif']['periodes'] = Array(Array(
					'dateDebut' => $dateMin->format('Y-m-d'),
					'dateFin' => $dateMax->format('Y-m-d'),
					'tarifs' => $tarifs,
					'type' => Array('elementReferenceType' => 'TarifTypePeriode', 'id' => 1304)
			)) ;
			$fieldlist[] = 'descriptionTarif.devise' ;
			$root['descriptionTarif']['devise'] = $_POST['devise'] ;
		}
	}

	/**
	 * Gestion des modes de paiement
	 */
	if ( isset($_POST['ModePaiement']) && is_array($_POST['ModePaiement']) && sizeof($_POST['ModePaiement']) > 0 )
	{
		$fieldlist[] = 'descriptionTarif.modesPaiement' ;
		$root['descriptionTarif']['modesPaiement'] = Array() ;
		foreach ( $_POST['ModePaiement'] as $id )
		{
			$root['descriptionTarif']['modesPaiement'][] = Array(
				'elementReferenceType' => 'ModePaiement',
				'id' => $id
			) ;
		}
	}

	/**
	 * TypesClientele
	 */
	if ( isset($_POST['TypeClientele']) && is_array($_POST['TypeClientele']) && sizeof($_POST['TypeClientele']) > 0 )
	{
		$fieldlist[] = 'prestations.typesClientele' ;
		$root['prestations']['typesClientele'] = [] ;
		foreach ( $_POST['TypeClientele'] as $id )
		{
			$root['prestations']['typesClientele'][] = [
				'elementReferenceType' => 'TypeClientele',
				'id' => $id
			 ] ;
		}
	}

	/**
	 * Generique
	 */
	if ( isset($_POST['FeteEtManifestationGenerique']) && $_POST['FeteEtManifestationGenerique'] != '' )
	{
		$fieldlist[] = 'informationsFeteEtManifestation.evenementGenerique' ;
		$root['informationsFeteEtManifestation']['evenementGenerique'] = [
				'elementReferenceType' => 'FeteEtManifestationGenerique',
				'id' => $_POST['FeteEtManifestationGenerique']
		] ;
	}

	/**
	 * Tourisme adapté / Handicap
	 */
	if ( isset($_POST['TourismeAdapte']) && is_array($_POST['TourismeAdapte']) && sizeof($_POST['TourismeAdapte']) > 0 )
	{
		$fieldlist[] = 'prestations.tourismesAdaptes' ;
		$root['prestations']['tourismesAdaptes'] = Array() ;
		foreach ( $_POST['TourismeAdapte'] as $id )
		{
			$root['prestations']['tourismesAdaptes'][] = Array(
				'elementReferenceType' => 'TourismeAdapte',
				'id' => $id
			) ;
		}
	}

	/**
	 * Réservation
	 */
	if ( isset($_POST['reservation']) && $_POST['reservation']['url'] != '' )
	{
		$fieldlist[] = 'reservation.organismes' ;
		$root['reservation']['organismes'] = [
			[
				'nom' => trim($_POST['reservation']['nom']) == '' ? 'Réservation' : trim($_POST['reservation']['nom']),
				'type' => [
					'elementReferenceType' => 'ReservationType',
					'id' => 475 // Directe
				],
				'moyensCommunication' => [
					[
						'type' => [
							'elementReferenceType' => 'MoyenCommunicationType',
							'id' => 205 // Site web (URL)
						],
						'coordonnees' => [
							'fr' => $_POST['reservation']['url']
						]
					]
				]
			]
		] ;
	}

	/**
	* Gestion des multimédias
	**/

	$illustrations = Array() ;
	$multimedias = Array() ;


	$keys_files = Array('illustrations','multimedias') ;
	foreach ( $keys_files as $kf )
	{
		if ( isset($_FILES[$kf]) )
		{
			foreach ( $_FILES[$kf]['error'] as $i => $error )
			{
				$media = Array(
					'name' => $_FILES[$kf]['name'][$i],
					'type' => $_FILES[$kf]['type'][$i],
					'tmp_name' => $_FILES[$kf]['tmp_name'][$i],
					'error' => $_FILES[$kf]['error'][$i],
					'size' => $_FILES[$kf]['size'][$i],
				) ;
				if ( $media['error'] == UPLOAD_ERR_NO_FILE ) continue ;
				if ( $media['error'] == UPLOAD_ERR_OK )
				{
					$finfo = new finfo(FILEINFO_MIME_TYPE) ;
					$ext = array_search( $finfo->file($media['tmp_name']),$configApidaeEvent['mimes_'.$kf],true ) ;
					if ( $ext !== false ) {
						
						$$kf[] = Array(
							'copyright'=>@$_POST[$kf][$i]['copyright'],
							'legende'=>@$_POST[$kf][$i]['legende'],
							'basename'=>basename($media['name']),
							'mime'=>$configApidaeEvent['mimes_'.$kf][$ext],
							'tempfile'=>$media['tmp_name']
						) ;
					}
					else
					{
						$ko[$kf.' '.($i+1)] = 'Type de fichier '.$finfo->file($media['tmp_name']).' interdit pour "'.$_FILES[$kf]['name'][$i]."\n".'<br />Formats acceptés : '.implode(',',$configApidaeEvent['mimes_'.$kf]) ;
					}
				}
				else
				{
					$ko[$kf.' '.($i+1)] = 'Erreur sur le fichier '.$kf.' '.($i+1).' : '.$media['error'] ;
				}
			}
		}
	}

	$medias = Array() ;
	if ( sizeof($illustrations) > 0 ) $root['illustrations'] = Array() ;
	foreach ( $illustrations as $i => $illus )
	{
		$medias['multimedia.illustration-'.($i+1)] = $apidaeEvent->getCurlValue($illus['tempfile'],$illus['mime'],$illus['basename']) ;
		$illustration = Array() ;
		$illustration['link'] = false ;
		$illustration['type'] = 'IMAGE' ;
		if ( $illus['legende'] != '' ) $illustration['nom'][$libelleXy] = $illus['legende'] ;
		if ( $illus['copyright'] != '' ) $illustration['copyright'][$libelleXy] = $illus['copyright'] ;
		$illustration['traductionFichiers'][0]['locale'] = 'fr' ;
		$illustration['traductionFichiers'][0]['url'] = 'MULTIMEDIA#illustration-'.($i+1) ;
		$root['illustrations'][] = $illustration ;
	}

	if ( sizeof($multimedias) > 0 ) $root['multimedias'] = Array() ;
	foreach ( $multimedias as $i => $mm )
	{
		$medias['multimedia.multimedia-'.($i+1)] = $apidaeEvent->getCurlValue($mm['tempfile'],$mm['mime'],$mm['basename']) ;
		$multimedia = Array() ;
		$multimedia['link'] = false ;
		$multimedia['type'] = 'DOCUMENT' ;
		if ( $mm['legende'] != '' ) $multimedia['nom'][$libelleXy] = $mm['legende'] ;
		if ( $mm['copyright'] != '' ) $multimedia['copyright'][$libelleXy] = $mm['copyright'] ;
		$multimedia['traductionFichiers'][0]['locale'] = 'fr' ;
		$multimedia['traductionFichiers'][0]['url'] = 'MULTIMEDIA#multimedia-'.($i+1) ;
		$root['multimedias'][] = $multimedia ;
	}
	
	$apidaeEvent->debug($illustrations,'$illustrations') ;
	$apidaeEvent->debug($medias,'$medias') ;

	if ( isset($root['illustrations']) && sizeof($root['illustrations']) > 0 ) $fieldlist[] = 'illustrations' ;
	if ( isset($root['multimedias']) && sizeof($root['multimedias']) > 0 ) $fieldlist[] = 'multimedias' ;
	
	if ( isset($_POST['rgpd']) && $_POST['rgpd'] == 1 ) {
		$root['enquete']['enquetes'][] = [
			'annee' => date('Y'),
			'retour' => true,
			'titre' => ['elementReferenceType' =>'EnqueteTitre', 'id' => $enquete_er_id] // Mise en conformité RGPD
		] ;
		$fieldlist[] = 'enquete.enquetes' ;
	}

	if ( $debug ) $timer->start('enregistrement') ;

	if ( $debug ) {
		$apidaeEvent->debug($root,$root) ;
	}

	if ( sizeof($ko) == 0 && ! $nosave )
	{
		/*
		if ( $debug )
		{
			echo '<pre>' ;
				echo json_encode($root,JSON_PRETTY_PRINT) ;
			echo '</pre>' ;
			echo '<pre>' ;
			echo json_encode($fieldlist,JSON_PRETTY_PRINT) ;
			echo '</pre>' ;
		}
		*/

		$enregistrer = Array(
			'fieldlist' => $fieldlist,
			'root' => $root,
			'medias' => $medias,
			'clientId' => $configApidaeEvent['projet_ecriture_clientId'],
			'secret' => $configApidaeEvent['projet_ecriture_secret']
		) ;
		if ( isset($infos_proprietaire['proprietaireId']) ) $enregistrer['proprietaireId'] = $infos_proprietaire['proprietaireId'] ;
		
		try {
			$ret_enr = $apidaeEvent->ajouter($enregistrer) ;
			if ( ! $ret_enr ) $ko[] = $ret_enr ;
		} catch ( ApidaeException $e ) {
			$details = $e->getDetails() ;
			if ( $debug )
			{
				echo '<pre>' ;
					print_r($e->getMessage()) ;
					print_r($details) ;
				echo '</pre>' ;
			}

			$ko['erreur'] = 'L\'offre n\'a pas été enregistrée sur Apidae...' ;
			$erreurs_debug[] = $details ;

			// Exemple : ECRITURE_INVALID_OBJET_TOURISTIQUE_DATA

			/**
			 * Exemple : El\u00e9ment de r\u00e9f\u00e9rence '0' non trouv\u00e9 pour le champ 'descriptionTarif.periodes[].tarifs[].type'
			 * El\u00e9ment r\u00e9f\u00e9rence '4101 - Forfait \/ engagement' interdit pour le champ obligatoire 'descriptionTarif.periodes[].tarifs[].type'
			 */
			if ( isset($details['message']) )
			{
				if ( preg_match('#descriptionTarif\.periodes\[\]\.tarifs\[\]\.type#',$details['message']) )
				{
					if ( preg_match('#\'0\' non trouv#',$details['message']) )
						$ko['details'] = __('Vous devez spécifier un type de tarif',false) ;
					else
						$ko['details'] = $details['message'] ;
				}
				else
				{
					if ( isset($details['errorType']) ) $ko['errorType'] = $details['errorType'] ;
					$ko['details'] = $details['message'] ;
				}
			}
			else
				$ko['details'] = $e->getMessage() ;
		} catch ( Exception $e ) {
			$ko['erreur'] = __('L\'offre n\'a pas été enregistrée sur Apidae...',false) ;
			$ko['details'] = $e->getMessage() ;
		}

		$apidaeEvent->debug($apidaeEvent->last_id,'last_id') ;
		if ( $apidaeEvent->last_id == "" && ! isset($ko['erreur']) ) $ko['erreur'] = __('L\'offre n\'a pas été créée sur Apidae pour une raison inconnue (last_id is null)...',false) ;
	}
	if ( $debug ) $timer->stop('enregistrement') ;
	
	$apidaeEvent->debug($ko,'$ko') ;

	if ( sizeof($ko) == 0 )
	{
		$post_mail = $_POST ;
		unset($post_mail['g-recaptcha-response']) ;

		$msg = 'Vous recevez ce mail parce qu\'un internaute a suggéré une manifestation sur ' ;
		$msg .= ( @$post_mail['referer'] != '' ) ? $post_mail['referer'] : $post_mail['script_uri'] ;
		$msg .= '.<br />' ;

		$msg .= 'Une offre ('.$apidaeEvent->last_id.') a été enregistrée comme brouillon sur Apidae.' ;
		$msg .= '<br />' ;

		$msg .= '<ul>' ;
		
		if ( $apidaeEvent->last_id != null )
			$msg .= '<li>Vous pouvez <strong><a href="'.$apidaeEvent->url_base().'gerer/objet-touristique/'.$apidaeEvent->last_id.'/modifier/">consulter le brouillon ici</a></strong></li>' ;

		$msg .= '<li>Vous pouvez également consulter la liste des offres en attente :<br />
		<strong>Gérer > Demandes API écriture > <a href="'.$apidaeEvent->url_base().'gerer/recherche-avancee/demandes-api-ecriture-a-valider/resultats/">Demandes d\'écriture à valider</a></strong>.</li>' ;

		$msg .= '</ul>' ;
		
		if ( isset($_POST['commentaire']) && trim($_POST['commentaire']) != '' )
		{
			$msg .= '<p style="color:#31708f;background:#d9edf7;border-color:#bce8f1;padding:10px;font-size:14px;">' ;
			$msg .= '<strong>La personne qui a saisi le formulaire a souhaité vous laisser un message (qui ne sera pas repris sur Apidae) :</strong><br />' ;
			$msg .= '<em>'.strip_tags(htmlentities($_POST['commentaire'])).'</em>' ;
			$msg .= '</p>' ;
		}

		$msg .= 'Vous trouverez ci-dessous un résumé brut des informations qui ont été enregistrées sur Apidae.<br />' ;

		$post_mail['message'] = $msg ;

		if ( sizeof($medias) > 0 )
			$post_mail['fichiers'] = $medias ;

		try {

			if ( $debug )
				$apidaeMembres = new ApidaeMembres(array_merge(
					$configApidaeMembres,
					['debug'=>true]
				)) ;
			else
				$apidaeMembres = new ApidaeMembres($configApidaeMembres) ;
			
			$membre = false ;
			if ( $debug ) $timer->start('getMembreById('.$infos_proprietaire['proprietaireId'].')') ;
			try {
				$membre = $apidaeMembres->getMembreById($infos_proprietaire['proprietaireId']) ;
			} catch ( Exception $e ) {
				$ko[] = 'Une erreur est survenue, merci de réessayer ultérieurement... ['.__LINE__.']' ;
			}
			if ( $debug ) $timer->stop('getMembreById('.$infos_proprietaire['proprietaireId'].')') ;
			
			if ( $membre )
			{
				$enr_dataLayer = Array(
					'event' => 'enregistrement',
					'commune_id' => $root['localisation']['adresse']['commune']['id'],
					'commune_nom' => $commune[2],
					'commune_cp' => $root['localisation']['adresse']['codePostal'],
					'membre_id' => $infos_proprietaire['proprietaireId'],
					'membre_nom' => $infos_proprietaire['structure_validatrice']
				) ;
				if ( isset($_GET['territoire']) )
				{
					$enr_dataLayer['territoire'] = $_GET['territoire'] ;
				}
				if ( preg_match('#^([0-9]{1,2})[0-9]{3}$#',$root['localisation']['adresse']['codePostal'],$match) )
				{
					$enr_dataLayer['departement'] = $match[1] ;
				}

				?><script>
					dataLayer.push(<?php echo json_encode($enr_dataLayer) ; ?>) ;
				</script><?php
			}
			elseif ( $debug )
			{
				echo '<pre>'.print_r($membre,true).'</pre>' ;
			}

		} catch ( Exception $e ) {
			if ( $debug )
			{
				echo '<pre>'.print_r($e,true).'</pre>' ;
			}
		}


		if ( isset($enr_dataLayer) ) $post_mail['dataLayer'] = json_encode($enr_dataLayer,JSON_PRETTY_PRINT) ;

		if ( $infos_proprietaire['mail_membre'] != null )
		{
			$objet = 'ApidaeEvent - ' . ( $debug ? '[debug] ' : '' ) . 'Nouvel enregistrement' ;
			$to = $debug ? $configApidaeEvent['mail_admin'] : $infos_proprietaire['mail_membre'] ;
			if ( ! isset($_POST['nomail']) )
			{
				if ( $debug ) $timer->start('mail_membre') ;
				$apidaeEvent->alerte($objet,$post_mail,$to) ;
				if ( $debug ) $timer->stop('mail_membre') ;
			}
			else
			{
				echo '<div class="alert alert-info">' ;
					echo '<h2>Objet</h2>' . $objet ;
					echo '<h2>To</h2>' . json_encode($to) ;
					echo '<h2>Message</h2>' ;
						echo '<pre>'.json_encode($post_mail,JSON_PRETTY_PRINT).'</pre>' ;
				echo '</div>' ;
			}
			unset($to) ;
			unset($objet) ;
		}
		$display_form = false ;

		$texte_offre_enregistree = '<p><i class="fas fa-check-circle"></i> '.__('<strong>Votre suggestion d\'événement a bien été enregistrée</strong>, nous vous remercions pour votre contribution.',false).'</p>'."\n".
		'<p><i class="fas fa-exclamation-circle"></i> '.__('<strong>Attention :</strong> Il ne sera visible sur les différents supports de communication alimentés par Apidae qu\'<strong>après validation</strong>.',false).'</p>'."\n" ;
		
		if ( isset($infos_proprietaire['url_structure_validatrice']) && $infos_proprietaire['url_structure_validatrice'] != '' )
		{
			$texte_offre_enregistree .= '<p>'.__('La validation est en cours auprès de').' : '.$infos_proprietaire['structure_validatrice'] ;
			$texte_offre_enregistree .= ' (<a href="'.$infos_proprietaire['url_structure_validatrice'].'" target="_blank">'.$infos_proprietaire['url_structure_validatrice'].'</a>)' ;
			$texte_offre_enregistree .= '</p>'."\n" ;
		}

		if ( isset($_POST['commentaire']) && trim($_POST['commentaire']) != '' )
			$texte_offre_enregistree .= '<p>'.__('Votre commentaire a également été transmis').' : "<em>'.htmlentities($_POST['commentaire']).'</em>"</p>'."\n" ;

		$url_consulter = 'https://base.apidae-tourisme.com/consulter/objet-touristique/'.$apidaeEvent->last_id ;
		//$texte_offre_enregistree .= '<p>Une fois validée, votre manifestation sera consultable sur <a onclick="window.open(this.href);return false;" href="'.$url_consulter.'">'.$url_consulter.'</a></p>' ;
		
		?>
			<?php $alert = addslashes(strip_tags($texte_offre_enregistree)) ; ?>
			<div class="alert alert-success" role="alert">
				<div id="texte_offre_enregistree"><?php echo $texte_offre_enregistree ; ?></div>
				
				<p><?php __('Plus d\'informations ici') ; ?> : <a href="https://www.apidae-tourisme.com" target="_blank">https://www.apidae-tourisme.com</a></p>
				<script>
					alert(jQuery('div#texte_offre_enregistree').text()) ;
				</script>
				<?php if ( isset($_SERVER['HTTP_REFERER']) ) { ?>
					<a href="<?php echo $_SERVER['HTTP_REFERER'] ; ?>" class="btn btn-primary"><i class="fas fa-plus-circle"></i> <?php __('Faire une autre suggestion de manifestation') ; ?></a>
				<?php } ?>
			</div>
		<?php


		if ( $debug )
		{
			if ( isset($_POST['nosave']) ) {
			?>
			<div class="alert alert-warning" role="alert">
				<p><i class="fas fa-exclamation-circle"></i> <strong>[debug + nosave] en réalité l'offre n'a pas été envoyée en enregistrement Apidae. Le debug + nosave traite cependant les envois de mail, qui seront uniquement envoyés au mail admin.</p>
			</div>
			<?php
			} else {
			?>
			<div class="alert alert-success" role="alert">
				<i class="fas fa-save"></i>
				<strong>[debug]</strong> Offre enregistrée en attente de validation dans Apidae :
				<p>On la retrouve dans <a href="https://base.apidae-tourisme.com/gerer/recherche-avancee/demandes-api-ecriture-a-valider/resultats/">Gérer > Demandes API écriture > Demandes d'écritures à valider</a></p>
			</div>
			<?php
			}
		}

		/**
		 * Envoi d'une notification au mail de contact ou de moyen de com'
		 */
		if ( isset($infos_orga['mail']) && $infos_orga['mail'] != '' && filter_var($infos_orga['mail'], FILTER_VALIDATE_EMAIL) )
		{
			$objet = 'ApidaeEvent - Votre suggestion de manifestation' ;
			$message = $texte_offre_enregistree ;
			$to = $debug ? $configApidaeEvent['mail_admin'] : $infos_orga['mail'] ;
			if ( ! isset($_POST['nomail']) )
			{
				if ( $debug ) $timer->start('mail_suggestion') ;
				$apidaeEvent->alerte($objet,$message,$to) ;
				if ( $debug ) $timer->stop('mail_suggestion') ;
			}
			if ( $debug )
			{
				?>
				<div class="alert alert-success" role="alert">
					<strong>[debug]</strong> Un mail organisateur a été trouvé : <?php echo $infos_orga['mail'] ; ?>
					<ul>
						<li><strong>Objet</strong> : <?php echo $objet ; ?></li>
						<li><strong>Message</strong> : <?php echo $message ; ?></li>
						<li><strong>To</strong> : <strike><?php echo $infos_orga['mail'] ; ?></strike> [debug] => <?php echo print_r($to,true) ; ?></li>
					</ul>
				</div>
				<?php
			}
		}
	}
	else
	{
		?>
		<div class="alert alert-danger" role="alert">
			<i class="fas fa-exclamation-circle"></i>
		  <span class="sr-only"><?php __('Erreur à l\'enregistrement') ; ?> :</span>
		  <strong><?php __('Une erreur s\'est produite et votre fiche n\'a pas pû être enregistrée.') ; ?></strong>
		  <?php
		  	if ( is_array($ko) )
		  	{
		  		echo '<ul>' ;
				  	foreach ( $ko as $k => $v )
				  	{
				  		echo '<li><strong>'.$k.'</strong> : '.$v.'</li>' ;
				  	}
			  	echo '</ul>' ;
		  	}
			if ( ! isset($_POST['nomail']) )
			{
				if ( $debug ) $timer->start('mails_erreur') ;
				$erreur_alerte = $ko ;
				$erreur_alerte['erreurs_debug'] = $erreurs_debug ;
				$erreur_alerte['_POST'] = $_POST ;
				$alerte = $apidaeEvent->alerte('ApidaeEvent - Erreur enregistrement',$erreur_alerte) ;
				if ( $debug ) $timer->stop('mails_erreur') ;
			}
		  ?>
		  <?php if ( isset($alerte) && $alerte === true ) { ?><br /><?php __('L\'erreur a été envoyée à un administrateur.') ; ?><?php } ?>
		  <br /><?php __('Veuillez nous excuser pour la gène occasionnée.') ; ?>
		  <br /><?php __('Vous pouvez essayer de nouveau d\'enregistrer ci-dessous, ou prendre contact avec l\'Office du Tourisme concernée par votre manifestation') ; ?> :
		  <ul>
			<li><?php echo $infos_proprietaire['structure_validatrice'] ; ?></li>
			<li><?php echo '<a href="'.$infos_proprietaire['url_structure_validatrice'].'" onclick="window.open(this.href);return false;">'.$infos_proprietaire['url_structure_validatrice'] ; ?></a></li>
			<li><?php echo '<a href="mailto:'.implode(',',$infos_proprietaire['mail_membre']).'">'.implode(', ',$infos_proprietaire['mail_membre']) ; ?></a></li>
		  </ul>
		</div>
		<?php
		$apidaeEvent->debug($ko,'$ko:'.__LINE__) ;
	}
	
	if ( $debug )
		$timer->display() ;