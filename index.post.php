<?php
	
	require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;

	if ( ! isset($_POST['commune']) ) return ;
	
	$verbose = true ;

	$commune = explode('|',$_POST['commune']) ;

	$id_membre = null ;
	
	// Si on est en multimembre, il faut absolument passer un proprietaireId
	if ( $_config['projet_ecriture_multimembre'] === true ) $id_membre = $_config['membre'] ;

	/*
	*	Par défait $clientId et $secret sont déjà définis en config.
	*	Cependant lors de la recherche du territoire concerné, on va regarder si pour le membre trouvé, on a un clientId et un secret de renseignés.
	*	Si c'est le cas, on va les préférer au clientId/secret défini par défaut dans $_config.
	*	L'objectif est de parer à l'absence d'API d'écriture multimembre, en attendant sa livraison. au 21/06/2017, on ne peut pas spécifier le membre propriétaire d'une fiche via l'API d'écriture, il faut donc une API d'écriture par membre, et donc un clientId/secret par membre.
	*/
	$clientId = null ;
	$secret = null ;
	$mail_membre = null ;

	$pma->debug($_POST,'$_POST') ;

	if ( $_config['recaptcha_secret'] != '' && ! $_config['debug'] )
	{
		$fields = array( 'secret' => $_config['recaptcha_secret'], 'response' => $_POST['g-recaptcha-response'] ) ;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		if ( ($result = curl_exec($ch)) === false )
		{
			var_dump(curl_error($ch)) ;
			$ko[] = 'Vérification du captcha impossible' ;
		}
		else
		{
			$json_result = json_decode($result) ;
			if ( ! $json_result->success ) $ko[] = 'L\'utilisateur n\'a pas coché la case "Je ne suis pas un robot"' ;
		}
	}

	/*
		Si on souhaite pouvoir écrire sur un membre différent en fonction de la commune saisie, alors dans la config on a renseigné $_config['membres'].
	*/
	if ( isset($_config['membres']) )
	{
		$territoires = Array() ;
		/* Dans ce cas, on va rechercher à quels territoires correspond la commune saisie. */
		$rq = $pma->mysqli->query(' select id_territoire from apidae_territoires T where id_commune = "'.$pma->mysqli->real_escape_string($commune[0]).'" ') or die($pma->mysqli->error) ;
		while ( $d = $rq->fetch_assoc() )
		{
			$territoires[] = $d['id_territoire'] ;
		}
		
		/* Au cas où la commune serait concernée par plusieurs territoires, on parcourt les membres dans l'ordre saisi pour choisir le premier dans la liste. */
		foreach ( $_config['membres'] as $m )
		{
			if ( in_array($m['id_territoire'],$territoires) )
			{
				// Si on n'a pas de projet d'écriture multimembre, on a besoin du clientId:secret du projet d'écriture du membre trouvé.
				if ( isset($m['secret']) && $m['secret'] !== null )
				{
					$clientId = $m['clientId'] ;
					$secret = $m['secret'] ;
				}
				// Si on n'a pas de projet d'écriture multimembre, ce paramètre ne servira à rien (impossible de modifier gestion.membreProprietaire.type.id)
				$id_membre = $m['id_membre'] ;
				$mail_membre = @$m['mail'] ;
				break ;
			}
		}
	}
	elseif ( isset($_config['membre']) )
		$id_membre = $_config['membre'] ;

	$root = Array() ;
	$fieldlist = Array() ;
	
	$root['type'] = 'FETE_ET_MANIFESTATION' ;

	if ( $_config['projet_ecriture_multimembre'] === true && $id_membre !== null )
	{
		/*
		*	Pour permettre l'utilisation conjointe des projets API écriture classique avec le projet API écriture global,
		*	on va regarder si on a trouvé un clientId:secret dans la config concernant le membre.
		*	Si on a rien trouvé, on est dans une API multi membre : on doit donc prendre le clientId:secret du projet API écriture global.
		*	Si clientId:secret sont renseignés, c'est qu'on est sur un membre ayant son propre projet d'écriture et n'étant pas abonné au projet API écriture multimembre : on utilise alors son clientId:secret.
		*/
		if ( $clientId == null && $secret == null )
		{
			// On n'a rien trouvé dans la config du membre : on prend le global
			$clientId = $_config['projet_ecriture_clientId'] ;
			$secret = $_config['projet_ecriture_secret'] ;
		}
		$proprietaireId = $id_membre ;
	}

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
		if ( ! $pma->verifDate($date['debut']) ) continue ;

		$date['debut'] = $pma->dateUs($date['debut']) ;
		$date['fin'] = $pma->dateUs($date['fin']) ;

		$db = new DateTime($date['debut']) ;
		
		$periode = Array() ;
		$periode['identifiantTemporaire'] = ( $i + 1 ) ;
		$periode['dateDebut'] = $date['debut'] ;
		$periode['dateFin'] = $pma->verifDate($date['fin']) ? $date['fin'] : $date['debut'] ;
		if ( $pma->verifTime($date['hdebut']) ) $periode['horaireOuverture'] = $date['hdebut'].":00" ;
		if ( $pma->verifTime($date['hfin']) ) $periode['horaireFermeture'] = $date['hfin'].":00" ;
		$periode['tousLesAns'] = false ;
		$periode['type'] = 'OUVERTURE_SAUF' ;
		if ( $date['complementHoraire'] != "" ) $periode['complementHoraire'] = Array('libelleFr' => trim($date['complementHoraire'])) ;

		$periodesOuvertures[] = $periode ;
		
	}
	if ( sizeof ($periodesOuvertures) > 0  )
	{
		$fieldlist[] = 'ouverture.periodesOuvertures' ;
		$root['ouverture']['periodesOuvertures'] = $periodesOuvertures ;
	}

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
		}
	}
	if ( sizeof($mcs) > 0 )
	{
		$fieldlist[] = 'informations.moyensCommunication' ;
		$root['informations']['moyensCommunication'] = $mcs ;
	}




	/*
		Contacts
	*/
	$contacts = Array() ;
	if ( isset($_POST['contact']) && is_array($_POST['contact']) && sizeof($_POST['contact']) > 0 )
	{
		
		foreach ( $_POST['contact'] as $post_contact )
		{
			if ( trim($post_contact['nom']) == '' && trim($post_contact['prenom']) == '' && trim($post_contact['fonction']) == '' ) continue ;
			$contact = Array() ;

			$contact['referent'] = true ;
			$contact['nom'] = $post_contact['nom'] ;
			$contact['prenom'] = $post_contact['prenom'] ;
			$mcs = Array() ;
			if ( $post_contact['mail'] != '' ) $mcs[] = Array(
				'type'=>Array('id'=>204,'elementReferenceType' => 'MoyenCommunicationType'),
				'coordonnees' => Array('fr' => $post_contact['mail'])
			) ;
			if ( $post_contact['telephone'] != '' ) $mcs[] = Array(
				'type'=>Array('id'=>201,'elementReferenceType' => 'MoyenCommunicationType'),
				'coordonnees' => Array('fr' => $post_contact['telephone'])
			) ;
			if ( $mcs > 0 ) $contact['moyensCommunication'] = $mcs ;

			if ( isset($post_contact['fonction']) && $post_contact['fonction'] != '' && $post_contact['fonction'] != 0 )
				$contact['fonction'] = Array('id'=>(int)$post_contact['fonction'],'elementReferenceType' => 'ContactFonction') ;
			

			$contacts[] = $contact ;
		}
	}
	if ( sizeof($contacts) > 0 )
	{
		$fieldlist[] = 'contacts' ;
		$root['contacts'] = $contacts ;
	}












	/**
		Gestion des types catégories themes
	**/
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
		Gestion des descriptifs
	**/
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
		Gestion des tarifs
	**/
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
			if ( $tarif['mini'] == '' && $tarif['maxi'] == '' ) continue ;
			$t = Array('devise' =>'EUR') ;
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
		}
	}





	/**
	* Gestion des multimédias
	**/

	$illustrations = Array() ;
	$multimedias = Array() ;

	$mimes = Array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif',) ;

	$key_files = 'medias' ;
	if ( isset($_FILES[$key_files]) )
	{
		foreach ( $_FILES['medias']['error'] as $i => $error )
		{
			$media = Array(
				'name' => $_FILES[$key_files]['name'][$i],
				'type' => $_FILES[$key_files]['type'][$i],
				'tmp_name' => $_FILES[$key_files]['tmp_name'][$i],
				'error' => $_FILES[$key_files]['error'][$i],
				'size' => $_FILES[$key_files]['size'][$i],
			) ;
			if ( $media['error'] == UPLOAD_ERR_NO_FILE ) continue ;
			if ( $media['error'] == UPLOAD_ERR_OK )
			{
				$finfo = new finfo(FILEINFO_MIME_TYPE) ;
				$ext = array_search( $finfo->file($media['tmp_name']),$mimes,true ) ;
			    if ( $ext !== false ) {
			        $legende = @$_POST[$key_files][$i]['legende'] ;
					$copyright = @$_POST[$key_files][$i]['copyright'] ;
					
					$illustrations[] = Array(
						'copyright'=>$copyright,
						'legende'=>$legende,
						'basename'=>basename($media['name']),
						'mime'=>$mimes[$ext],
						'tempfile'=>$media['tmp_name']
					) ;
			    }
				else
				{
					$ko[] = 'Type de fichier interdit pour la photo '.($i+1).' : '.$ext ;
				}
			}
			else
			{
				$ko[] = 'Erreur sur la photo '.($i+1).' : '.$media['error'] ;
			}
		}
	}


	$medias = Array() ;
	if ( sizeof($illustrations) > 0 ) $root['illustrations'] = Array() ;
	foreach ( $illustrations as $i => $illus )
	{
		$medias['multimedia.illustration-'.($i+1)] = $pma->getCurlValue($illus['tempfile'],$illus['mime'],$illus['basename']) ;
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
		$medias['multimedia.multimedia-'.($i+1)] = $pma->getCurlValue($mm['tempfile'],$mm['mime'],$mm['basename']) ;
		$multimedia = Array() ;
		$multimedia['link'] = false ;
		$multimedia['type'] = 'PLAN' ;
		$multimedia['traductionFichiers'][0]['locale'] = 'fr' ;
		$multimedia['traductionFichiers'][0]['url'] = 'MULTIMEDIA#multimedia-'.($i+1) ;
		$root['multimedias'][] = $multimedia ;
	}
	
	$pma->debug($illustrations,'$illustrations') ;
	$pma->debug($medias,'$medias') ;

	if ( isset($root['illustrations']) && sizeof($root['illustrations']) > 0 ) $fieldlist[] = 'illustrations' ;
	if ( isset($root['multimedias']) && sizeof($root['multimedias']) > 0 ) $fieldlist[] = 'multimedias' ;
	
	if ( sizeof($ko) == 0 )
	{
		$enregistrer = Array(
			'fieldlist' => $fieldlist,
			'root' => $root,
			'medias' => $medias,
			'proprietaireId' => $proprietaireId,
			'clientId' => $clientId,
			'secret' => $secret
		) ;
		$ko = $pma->ajouter($enregistrer) ;
	}
	
	$pma->debug($ko,'$ko') ;

	if ( $ko === true )
	{
		$post_mail = $_POST ;
		unset($post_mail['g-recaptcha-response']) ;

		$msg = 'Vous recevez ce mail parce qu\'un internaute a suggéré une manifestation sur ' ;
		$msg .= ( @$post_mail['referer'] != '' ) ? $post_mail['referer'] : $post_mail['script_uri'] ;
		$msg .= '.<br />' ;

		$msg .= 'Une offre ('.$pma->last_id.') a été enregistrée comme brouillon sur Apidae.' ;
		$msg .= '.<br />' ;

		$msg .= '<ul>' ;
		
		if ( $pma->last_id != null )
			$msg .= '<li>Vous pouvez <strong><a href="'.$pma->url_base().'gerer/objet-touristique/'.$pma->last_id.'/modifier/">consulter le brouillon ici</a></strong></li>' ;

		$msg .= '<li>Vous pouvez également consulter la liste des offres en attente :<br />
		<strong>Gérer > Demandes API écriture > <a href="'.$pma->url_base().'gerer/recherche-avancee/demandes-api-ecriture-a-valider/resultats/">Demandes d\'écriture à valider</a></strong>.</li>' ;

		$msg .= '</ul>' ;

		$msg .= 'Vous trouverez ci-dessous un résumé brut des informations qui ont été enregistrées sur Apidae.<br /><br />' ;
		$post_mail['message'] = $msg ;

		//$alerte = $pma->alerte('enregistrement [admin]',$post_mail) ;
		if ( $mail_membre != null )
		{
			$pma->alerte('Nouvel enregistrement',$post_mail,$mail_membre) ;
		}
		$display_form = false ;
		?>
			<div class="alert alert-success" role="alert">
				<span class="glyphicon glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
				<strong>Offre enregistrée :</strong>
				<p>/!\ Votre événement a bien été enregistré, nous vous remercions pour votre contribution.</p>
				<p>Il a été envoyé en validation et devrait apparaître d'ici 24 à 48h sur les différents supports de communication alimentés par Apidae.</p>
				<p>Plus d'informations ici : <a href="http://www.apidae-tourisme.com" target="_blank">http://www.apidae-tourisme.com</a></p>
			</div>
		<?php
		
		if ( $pma->debug )
		{
			?>
			<div class="alert alert-success" role="alert">
				<span class="glyphicon glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
				<strong>[DEBUG] Offre enregistrée en attente de validation dans Apidae :</strong>
				<p>On la retrouve dans <a href="https://base.apidae-tourisme.com/gerer/recherche-avancee/demandes-api-ecriture-a-valider/resultats/">Gérer > Demandes API écriture > Demandes d'écritures à valider</a></p>
				<p>L'offre a été enregistrée grâce au clientId "<?php echo $clientId ; ?>" du projet d'écriture. Elle a été rattachée au membre <a href="https://base.apidae-tourisme.com/echanger/membre-sitra/1147" target="_blank"><?php echo $id_membre ; ?></a></p>
			</div>
			<?php
		}
	}
	else
	{
		?>
		<div class="alert alert-danger" role="alert">
		  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
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
		  	$alerte = $pma->alerte('Erreur enregistrement',$ko) ;
		  	$alerte = $pma->alerte('Erreur enregistrement',$_POST) ;
		  ?>
		  <?php if ( $alerte === true ) { ?><br />L'erreur a été envoyée à un administrateur.<?php } ?>
		  <br />Veuillez nous excuser pour la gène occasionnée.
		  <br />Vous pouvez essayer de nouveau d'enregistrer ci-dessous, ou prendre contact avec l'Office du Tourisme concernée par votre manifestation.
		</div>
		<?php
		$pma->debug($ko) ;
	}
	