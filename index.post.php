<?php
	
	require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;
	
	if ( ! isset($_POST['commune']) ) return ;
	
	$verbose = true ;

	$commune = explode('|',$_POST['commune']) ;

	/**
	 * Fail over : si on a trouvé aucun propriétaire dans la liste 
	 */

	$infos_proprietaire = Array(
		'mail_membre' => $configApidaeEvent['mail_admin'],
		'structure_validatrice' => $configApidaeEvent['nom_membre'],
		'url_structure_validatrice' => $configApidaeEvent['url_membre'],
		'proprietaireId' => $configApidaeEvent['membre']
	) ;
	$ApidaeEvent->debug($infos_proprietaire,'$infos_proprietaire '.__LINE__) ;

		
	$infos_orga = Array() ;

	if ( ! isset($ko) ) $ko = Array() ;

	$ApidaeEvent->debug($_POST,'$_POST') ;

	/**
	 * $nosave définit s'il faut envoyer en enregistrement sur Apidae ou non.
	 * Il n'a pas d'impact sur l'envoi des mails
	 */
	$nosave = isset($_POST['nosave']) && $configApidaeEvent['debug'] ;

	if ( $configApidaeEvent['recaptcha_secret'] != '' && ! $configApidaeEvent['debug'] )
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
			$ko['Captcha'] = 'Vérification du captcha impossible' ;
		}
		else
		{
			$json_result = json_decode($result) ;
			if ( ! $json_result->success ) $ko['Captcha'] = 'L\'utilisateur n\'a pas coché la case "Je ne suis pas un robot"' ;
		}
	}

	/**
	*	Si on souhaite pouvoir écrire sur un membre différent en fonction de la commune saisie, alors dans la config on a renseigné $configApidaeEvent['membres'].
	*/

	if ( $configApidaeEvent['projet_ecriture_multimembre'] === true )
	{
		require_once(realpath(dirname(__FILE__)).'/territoires.inc.php') ;
		/**
		 * On commence par récupérer la liste des membres abonnés au projet d'écriture et qui ont les droits sur la commune concernée.
		*/

		$ApidaeEvent->debug($commune,'$commune') ;

		$membresCommune = $ApidaeMembres->getMembres(
			Array(
					'communeCode'=>$commune[3], // communeCode = code INSEE
					//"idProjet" => $configApidaeEvent['projet_ecriture_projetId']
			)
			//Array("COMMUNES")
		) ;
		// Au 19/02/2021

		$ApidaeEvent->debug($membresCommune,'$membresCommune') ;

		$membresConcernes = Array() ;
		foreach ( $membresCommune as $mb )
			$membresConcernes[$mb['id']] = $mb ;
		
		$ApidaeEvent->debug($membresConcernes,'$membresConcernes') ;

		$doubleCheck = true ;
		if ( ! isset($territoires) || ! is_array($territoires) ) $doubleCheck = false ;
				
		if ( isset($configApidaeEvent['membres']) )
		{
			/* Au cas où la commune serait concernée par plusieurs territoires, on parcoure les membres dans l'ordre saisi pour choisir le premier dans la liste. */
			foreach ( $configApidaeEvent['membres'] as $m )
			{
				/**
				 * On trouve le premier membre concerné (dont une commune sur Apidae correspond à la commune de la manif)
				 * */
				//$ApidaeEvent->debug(isset($membresConcernes[$m['id_membre']]),'isset($membresConcernes['.$m['id_membre'].']) ? '.$m['nom']) ;
				if ( isset($membresConcernes[$m['id_membre']]) )
				{
					$ApidaeEvent->debug($m['id_membre'],'membre '.$m['nom'].' concerné (boucle)') ;
					$trouve = true ;
					/**
					 * Ce n'est pas suffisant : il faut aussi s'assurer que dans la config c'était bien le territoire choisi
					 */
					if ( $doubleCheck )
					{
						$ApidaeEvent->debug('Double check... $commune[3] = '.$commune[3]) ;
						$ApidaeEvent->debug(@$m['insee_communes'],'insee_communes') ;
						$trouve = false ;
						if ( 
							isset($m['insee_communes']) 
							&& is_array($m['insee_communes']) 
							&& in_array($commune[3],$m['insee_communes'])
						)
						{
							// Trouvé dans la liste des communes spécifiée en config !
							$ApidaeEvent->debug('trouvé dans les insee_communes !') ;
							$trouve = true ;
						}

						$ApidaeEvent->debug(@array_keys(@$territoires[$m['id_territoire']]->perimetre),'Recherche de '.$commune[3].' dans le territoire '.$m['id_territoire'].' du membre...') ;
						if (
							isset($m['id_territoire']) && isset($territoires) && is_array($territoires)
							&& isset($territoires[$m['id_territoire']])
							&& isset($territoires[$m['id_territoire']]->perimetre[$commune[3]])
						)
						{
							// Trouvé dans le territoire de la config !
							$ApidaeEvent->debug('trouvé dans le territoire !') ;
							$trouve = true ;
						}
					}

					if ( $trouve )
					{
						$ApidaeEvent->debug($m,'membre trouvé...') ;
						$infos_proprietaire['proprietaireId'] = $m['id_membre'] ;
						$infos_proprietaire['mail_membre'] = @$m['mail'] ;
						$infos_proprietaire['structure_validatrice'] = $m['nom'] ;
						$infos_proprietaire['url_structure_validatrice'] = $m['site'] ;
						break ;
					}
				}
			}
		}
	}

	$ApidaeEvent->debug($infos_proprietaire,'$infos_proprietaire') ;

	$root = Array() ;
	$fieldlist = Array() ;
	
	$root['type'] = 'FETE_ET_MANIFESTATION' ;

	$fieldlist[] = 'nom' ;
	$root['nom']['libelleFr'] = $_POST['nom'] ;

	$fieldlist[] = 'informationsFeteEtManifestation.nomLieu' ;
	$root['informationsFeteEtManifestation']['nomLieu'] = $_POST['lieu'] ;

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
		if ( sizeof($date) <= 4 ) continue ;
		if ( ! $ApidaeEvent->verifDate($date['debut']) ) continue ;

		$date['debut'] = $ApidaeEvent->dateUs($date['debut']) ;
		$date['fin'] = $ApidaeEvent->dateUs($date['fin']) ;

		$db = new DateTime($date['debut']) ;
		
		$periode = Array() ;
		$periode['identifiantTemporaire'] = ( $i + 1 ) ;
		$periode['dateDebut'] = $date['debut'] ;
		$periode['dateFin'] = $ApidaeEvent->verifDate($date['fin']) ? $date['fin'] : $date['debut'] ;
		if ( $ApidaeEvent->verifTime($date['hdebut']) ) $periode['horaireOuverture'] = $date['hdebut'].":00" ;
		if ( $ApidaeEvent->verifTime($date['hfin']) ) $periode['horaireFermeture'] = $date['hfin'].":00" ;
		$periode['tousLesAns'] = false ;
		$periode['type'] = 'OUVERTURE_TOUS_LES_JOURS' ;
		if ( $date['complementHoraire'] != "" ) $periode['complementHoraire'] = Array('libelleFr' => trim($date['complementHoraire'])) ;

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
			$ko['Contact obligatoire'] = 'merci de préciser au moins 1 mail ou 1 numéro de téléphone dans la ligne "contact".' ;
		}

	}
	elseif ( isset($_GET['contactObligatoire']) && $_GET['contactObligatoire'] == 1 )
		$ko['Contact obligatoire'] = 'merci de préciser au moins 1 contact' ;

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
		$root['presentation']['descriptifCourt']['libelleFr'] = trim($_POST['descriptifCourt']) ;
	}
	if ( isset($_POST['descriptifDetaille']) && trim($_POST['descriptifDetaille']) != "" )
	{
		$fieldlist[] = 'presentation.descriptifDetaille' ;
		$root['presentation']['descriptifDetaille']['libelleFr'] = trim($_POST['descriptifDetaille']) ;
	}

	/**
	 * Descriptifs thématisés
	 */
	$dts = Array() ;
	if ( isset($_POST['descriptifsThematises']) )
	{
		foreach ( $_POST['descriptifsThematises'] as $id => $libelleFr )
		{
			if ( trim($libelleFr) != '' )
			{
				$dts[] = Array(
					'theme' => Array(
						'elementReferenceType' => 'DescriptifTheme',
						'id' => $id
					),
					'description' => Array(
						'libelleFr' => trim($libelleFr)
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
	if ( isset($_POST['descriptionTarif_complement_libelleFr']) )
	{
		$fieldlist[] = 'descriptionTarif.complement' ;
		$root['descriptionTarif']['complement']['libelleFr'] = trim($_POST['descriptionTarif_complement_libelleFr']) ;	
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
			if ( isset($tarif['precisions']) ) $t['precisionTarif']['libelleFr'] = $tarif['precisions'] ;
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
						$ko[$kf.' '.($i+1)] = 'Type de fichier interdit pour le fichier '.$kf.' '.($i+1).' : '.$ext ;
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
		$medias['multimedia.illustration-'.($i+1)] = $ApidaeEvent->getCurlValue($illus['tempfile'],$illus['mime'],$illus['basename']) ;
		$illustration = Array() ;
		$illustration['link'] = false ;
		$illustration['type'] = 'IMAGE' ;
		if ( $illus['legende'] != '' ) $illustration['nom']['libelleFr'] = $illus['legende'] ;
		if ( $illus['copyright'] != '' ) $illustration['copyright']['libelleFr'] = $illus['copyright'] ;
		$illustration['traductionFichiers'][0]['locale'] = 'fr' ;
		$illustration['traductionFichiers'][0]['url'] = 'MULTIMEDIA#illustration-'.($i+1) ;
		$root['illustrations'][] = $illustration ;
	}

	if ( sizeof($multimedias) > 0 ) $root['multimedias'] = Array() ;
	foreach ( $multimedias as $i => $mm )
	{
		$medias['multimedia.multimedia-'.($i+1)] = $ApidaeEvent->getCurlValue($mm['tempfile'],$mm['mime'],$mm['basename']) ;
		$multimedia = Array() ;
		$multimedia['link'] = false ;
		$multimedia['type'] = 'DOCUMENT' ;
		if ( $mm['legende'] != '' ) $multimedia['nom']['libelleFr'] = $mm['legende'] ;
		if ( $mm['copyright'] != '' ) $multimedia['copyright']['libelleFr'] = $mm['copyright'] ;
		$multimedia['traductionFichiers'][0]['locale'] = 'fr' ;
		$multimedia['traductionFichiers'][0]['url'] = 'MULTIMEDIA#multimedia-'.($i+1) ;
		$root['multimedias'][] = $multimedia ;
	}
	
	$ApidaeEvent->debug($illustrations,'$illustrations') ;
	$ApidaeEvent->debug($medias,'$medias') ;

	if ( isset($root['illustrations']) && sizeof($root['illustrations']) > 0 ) $fieldlist[] = 'illustrations' ;
	if ( isset($root['multimedias']) && sizeof($root['multimedias']) > 0 ) $fieldlist[] = 'multimedias' ;
	
	if ( sizeof($ko) == 0 && ! $nosave )
	{
		$enregistrer = Array(
			'fieldlist' => $fieldlist,
			'root' => $root,
			'medias' => $medias,
			'clientId' => $configApidaeEvent['projet_ecriture_clientId'],
			'secret' => $configApidaeEvent['projet_ecriture_secret']
		) ;
		if ( isset($infos_proprietaire['proprietaireId']) ) $enregistrer['proprietaireId'] = $infos_proprietaire['proprietaireId'] ;
		
		try {
			$ret_enr = $ApidaeEvent->ajouter($enregistrer) ;
			if ( ! $ret_enr ) $ko[] = $ret_enr ;
		} catch ( Exception $e ) {
			$ko[] = 'L\'offre n\'a pas été enregistrée sur Apidae...' ;
			$ko[] = $e->getMessage() ;
		}

		$ApidaeEvent->debug($ApidaeEvent->last_id,'last_id') ;
		if ( $ApidaeEvent->last_id == "" ) $ko[] = 'L\'offre n\'a pas été créée sur Apidae pour une raison inconnue (last_id is null)...' ;
	}
	
	$ApidaeEvent->debug($ko,'$ko') ;

	if ( sizeof($ko) == 0 )
	{
		$post_mail = $_POST ;
		unset($post_mail['g-recaptcha-response']) ;

		$msg = 'Vous recevez ce mail parce qu\'un internaute a suggéré une manifestation sur ' ;
		$msg .= ( @$post_mail['referer'] != '' ) ? $post_mail['referer'] : $post_mail['script_uri'] ;
		$msg .= '.<br />' ;

		$msg .= 'Une offre ('.$ApidaeEvent->last_id.') a été enregistrée comme brouillon sur Apidae.' ;
		$msg .= '<br />' ;

		$msg .= '<ul>' ;
		
		if ( $ApidaeEvent->last_id != null )
			$msg .= '<li>Vous pouvez <strong><a href="'.$ApidaeEvent->url_base().'gerer/objet-touristique/'.$ApidaeEvent->last_id.'/modifier/">consulter le brouillon ici</a></strong></li>' ;

		$msg .= '<li>Vous pouvez également consulter la liste des offres en attente :<br />
		<strong>Gérer > Demandes API écriture > <a href="'.$ApidaeEvent->url_base().'gerer/recherche-avancee/demandes-api-ecriture-a-valider/resultats/">Demandes d\'écriture à valider</a></strong>.</li>' ;

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


		/**
		 * Maj DU 15/02/2021
		 * Récupérations d'infos pour Analytics
		 * 
		 * 
		 */
		/*
		<script>
				dataLayer.push({
					'event' : 'enregistrement',
					'departement' : 3,
					'territoire' : 3337048,
					'membre' : 1336
				}) ;
			</script>
		*/

		try {

			if ( $configApidaeEvent['debug'] )
				$ApidaeMembres = new \PierreGranger\ApidaeMembres(array_merge(
					$configApidaeMembres,
					array('debug'=>true)
				)) ;
			else
				$ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;
			$membre = $ApidaeMembres->getMembreById($infos_proprietaire['proprietaireId']) ;
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
			elseif ( $configApidaeEvent['debug'] )
			{
				echo '<pre>'.print_r($membre,true).'</pre>' ;
			}

		} catch ( Exception $e ) {
			if ( $configApidaeEvent['debug'] )
			{
				echo '<pre>'.print_r($e,true).'</pre>' ;
			}
		}


		if ( isset($enr_dataLayer) ) $post_mail['dataLayer'] = json_encode($enr_dataLayer,JSON_PRETTY_PRINT) ;

		if ( $infos_proprietaire['mail_membre'] != null )
		{
			$objet = ( $configApidaeEvent['debug'] ? '[debug] ' : '' ) . 'Nouvel enregistrement' ;
			$to = $configApidaeEvent['debug'] ? $configApidaeEvent['mail_admin'] : $infos_proprietaire['mail_membre'] ;
			$ApidaeEvent->alerte($objet,$post_mail,$to) ;
			unset($to) ;
			unset($objet) ;
		}
		$display_form = false ;

		$texte_offre_enregistree = '<p><i class="fas fa-check-circle"></i> <strong>Votre suggestion d\'événement a bien été enregistrée</strong>, nous vous remercions pour votre contribution.</p>
		<p><i class="fas fa-exclamation-circle"></i> <strong>Attention :</strong> Il a été envoyé en validation, et devrait être visible 24 à 48h <strong>après sa validation</strong> par votre office de tourisme, sur les différents supports de communication alimentés par Apidae.</p>' ;
		
		if ( isset($infos_proprietaire['url_structure_validatrice']) && $infos_proprietaire['url_structure_validatrice'] != '' )
		{
			$texte_offre_enregistree .= '<p>La validation est en cours auprès de : '.$infos_proprietaire['structure_validatrice'] ;
			$texte_offre_enregistree .= ' (<a href="'.$infos_proprietaire['url_structure_validatrice'].'" target="_blank">'.$infos_proprietaire['url_structure_validatrice'].'</a>)' ;
			$texte_offre_enregistree .= '</p>' ;
		}

		if ( isset($_POST['commentaire']) && trim($_POST['commentaire']) != '' )
			$texte_offre_enregistree .= '<p>Votre commentaire a également été transmis : "<em>'.htmlentities($_POST['commentaire']).'</em>"</p>' ;

		$url_consulter = 'https://base.apidae-tourisme.com/consulter/objet-touristique/'.$ApidaeEvent->last_id ;
		//$texte_offre_enregistree .= '<p>Une fois validée, votre manifestation sera consultable sur <a onclick="window.open(this.href);return false;" href="'.$url_consulter.'">'.$url_consulter.'</a></p>' ;
		
		?>
			<?php $alert = addslashes(strip_tags($texte_offre_enregistree)) ; ?>
			<div class="alert alert-success" role="alert">
				<div id="texte_offre_enregistree"><?php echo $texte_offre_enregistree ; ?></div>
				<p>Plus d'informations ici : <a href="https://www.apidae-tourisme.com" target="_blank">https://www.apidae-tourisme.com</a></p>
				<script>
					alert(jQuery('div#texte_offre_enregistree').text()) ;
				</script>
				<?php if ( isset($_SERVER['HTTP_REFERER']) ) { ?>
					<a href="<?php echo $_SERVER['HTTP_REFERER'] ; ?>" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Faire une autre suggestion de manifestation</a>
				<?php } ?>
			</div>
		<?php


		if ( $configApidaeEvent['debug'] )
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
			$objet = 'Votre suggestion de manifestation' ;
			$message = $texte_offre_enregistree ;
			$to = $configApidaeEvent['debug'] ? $configApidaeEvent['mail_admin'] : $infos_orga['mail'] ;
			$ApidaeEvent->alerte($objet,$message,$to) ;
			if ( $configApidaeEvent['debug'] )
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
		  <span class="sr-only">Erreur à l'enregistrement :</span>
		  <strong>Une erreur s'est produite et votre fiche n'a pas pû être enregistrée.</strong>
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
		  	$alerte = $ApidaeEvent->alerte('Erreur enregistrement',$ko) ;
		  	$alerte = $ApidaeEvent->alerte('Erreur enregistrement',$_POST) ;
		  ?>
		  <?php if ( $alerte === true ) { ?><br />L'erreur a été envoyée à un administrateur.<?php } ?>
		  <br />Veuillez nous excuser pour la gène occasionnée.
		  <br />Vous pouvez essayer de nouveau d'enregistrer ci-dessous, ou prendre contact avec l'Office du Tourisme concernée par votre manifestation :
		  <ul>
			<li><?php echo $infos_proprietaire['structure_validatrice'] ; ?></li>
			<li><?php echo '<a href="'.$infos_proprietaire['url_structure_validatrice'].'" onclick="window.open(this.href);return false;">'.$infos_proprietaire['url_structure_validatrice'] ; ?></a></li>
			<li><?php echo '<a href="mailto:'.implode(',',$infos_proprietaire['mail_membre']).'">'.implode(', ',$infos_proprietaire['mail_membre']) ; ?></a></li>
		  </ul>
		</div>
		<?php
		$ApidaeEvent->debug($ko,'$ko:'.__LINE__) ;
	}
	