<?php


require_once(realpath(dirname(__FILE__)) . '/requires.inc.php');

?><!DOCTYPE html>
<html lang="fr">

<head>
	<?php include('head.inc.php') ; ?>
</head>

<body>
	<div class="container">

		<?php

		if (isset($_GET['testAnalytics'])) {
			$enr_dataLayer = array(
				'event' => 'enregistrement',
				'commune_id' => '1',
				'commune_nom' => 'test',
				'commune_cp' => '99999',
				'membre_id' => 1,
				'membre_nom' => 'test',
				'territoire' => 1,
				'departement' => 99
			);
		?><script>
				dataLayer.push(<?php echo json_encode($enr_dataLayer); ?>);
			</script><?php
					}

					$ko = array();
					$ok = array();

					$display_form = true;

					if (isset($_POST['nom'])) {
						include(realpath(dirname(__FILE__)) . '/index.post.php');
					}

					$post = $_POST;
					if (!is_array($post)) $post = array();

					$token_test = $apidaeEvent->gimme_token();

					if (!$token_test) {
						$display_form = false;
						?>
			<div class="alert alert-danger" role="alert">
				<i class="fas fa-exclamation"></i>
				<span class="sr-only">Formulaire indisponible :</span>
				<strong>Un problème technique empêche l'utilisation du formulaire actuellement.</strong>
				<br />Veuillez nous excuser pour la gène occasionnée.
				<br />Vous pouvez prendre contact avec l'Office du Tourisme concernée par votre manifestation, ou revenir sur cette page plus tard.
			</div>
		<?php
					}

		?>


		<?php if ($display_form) { ?>

			<?php
			if ($configApidaeEvent['debug'] && isset($_GET['showAbonnes']))
				include(realpath(dirname(__FILE__)) . '/showAbonnes.inc.php');
			?>

			<form class="form" method="post" enctype="multipart/form-data" novalidate>

				<?php $referer = (isset($_POST['referer'])) ? $_POST['referer'] : @$_SERVER['HTTP_REFERER']; ?>
				<input type="hidden" name="referer" value="<?php echo htmlentities($referer); ?>" />
				<input type="hidden" name="devise" value="<?php echo htmlentities($devise_apidae); ?>" />

				<fieldset class="form-group required">
					<legend>Nom de la manifestation</legend>
					<div class="controls">
						<input class="form-control form-control-lg" name="nom" type="text" value="<?php echo htmlentities(@$post['nom']); ?>" id="nom" required="required" />
					</div>
				</fieldset>

				<fieldset class="form-group">

					<legend>Importance de votre événement</legend>

					<div class="form-group row required">

						<label for="portee" class="<?php echo $class_label; ?> col-form-label">
							Portée
							<i class="fas fa-info-circle" title="La portée concerne les spectateurs et la distance qu’ils sont prêt à parcourir pour participer à une manifestation."></i>
						</label>
						<div class="<?php echo $class_champ; ?>">
							<select class="form-control" name="portee" id="portee" required="required">
								<option value="">-</option>
								<?php

								$FeteEtManifestationPortees = $apidaeEvent->getElementsReferenceByType('FeteEtManifestationPortee');
								foreach ($FeteEtManifestationPortees as $option) {
									echo '<option value="' . $option['id'] . '"';
									if (isset($option['description'])) echo ' title="' . htmlspecialchars($option['description']) . '" ';
									if (isset($post['portee']) && $post['portee'] == $option['id']) echo ' selected="selected"';
									echo '>' . $option['libelleFr'] . '</option>';
								}

								?>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="nbParticipantsAttendu" class="<?php echo $class_label; ?> col-form-label">Participants attendus</label>
						<div class="col-md-4 col-sm-4">
							<input class="form-control" type="number" name="nbParticipantsAttendu" id="nbParticipantsAttendu" value="<?php echo htmlentities(@$post['nbParticipantsAttendu']); ?>" />
						</div>
						<label for="nbVisiteursAttendu" class="<?php echo $class_label; ?> col-form-label">Visiteurs attendus</label>
						<div class="col-md-4 col-sm-4">
							<input class="form-control" type="number" name="nbVisiteursAttendu" id="nbVisiteursAttendu" value="<?php echo htmlentities(@$post['nbVisiteursAttendu']); ?>" />
						</div>
					</div>
				</fieldset>

				<fieldset class="form-group">
					<legend>Adresse</legend>
					<div class="form-group row">
						<label for="adresse1" class="<?php echo $class_label; ?> col-form-label">Adresse 1
							<i class="fas fa-info-circle" title="Voie et bâtiment. Exemple : 60 rue des Lilas - Bâtiment A. Pas de virgule mais un espace entre le numéro et le nom de la rue."></i>
						</label>
						<div class="<?php echo $class_champ; ?>">
							<input class="form-control" type="text" name="adresse1" value="<?php echo htmlentities(@$post['adresse1']); ?>" />
						</div>
					</div>
					<div class="form-group row">
						<label for="adresse2" class="<?php echo $class_label; ?> col-form-label">Adresse 2
							<i class="fas fa-info-circle" title="Lieu-dit, zone d’activité, BP (pour boite postale)…"></i>
						</label>
						<div class="<?php echo $class_champ; ?>">
							<input class="form-control" type="text" name="adresse2" value="<?php echo htmlentities(@$post['adresse2']); ?>" />
						</div>
					</div>
					<div class="form-group row">
						<label for="adresse3" class="<?php echo $class_label; ?> col-form-label">Adresse 3
							<i class="fas fa-info-circle" title="Niveau de la station et/ou le quartier si nécessaire. Exemple : Morillon village et Morillon 1100."></i>
						</label>
						<div class="<?php echo $class_champ; ?>">
							<input class="form-control" type="text" name="adresse3" value="<?php echo htmlentities(@$post['adresse3']); ?>" />
						</div>
					</div>
					<?php
					
					$communes = null;
					if (isset($_GET['communes'])) {
						$communes = $apidaeEvent->getCommunesByInsee(explode(',', $_GET['communes']));
					} elseif (isset($configApidaeEvent['communes_insee'])) {
						$communes = $apidaeEvent->getCommunesByInsee(explode(',', $configApidaeEvent['communes_insee']));
					} elseif (isset($configApidaeEvent['territoire'])) {
						$communes = $apidaeEvent->getCommunesByTerritoire($configApidaeEvent['territoire'], isset($_GET['refresh']));
					}

					if (!is_array($communes) || sizeof($communes) == 0) {
						//$apidaeEvent->alerte('Liste communes introuvable',$_GET) ;
					?>
						<div class="alert alert-danger" role="alert">
							<i class="fas fa-exclamation"></i>
							<strong>Impossible de récupérer la liste de communes...</strong>
							<br />Veuillez nous excuser pour la gène occasionnée.
							<br />Vous pouvez prendre contact avec l'<a href="https://www.apidae-tourisme.com/apidae-tourisme/carte-du-reseau/" target="_blank">Office du Tourisme concernée par votre manifestation</a>.
						</div>
					<?php
						die();
					}

					@uasort($communes, function ($a, $b) {
						$unwanted_array = array(
							'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
							'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
							'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
							'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
							'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y'
						);
						return strtr($a['nom'], $unwanted_array) > strtr($b['nom'], $unwanted_array);
					});

					?>
					<div class="form-group row required">
						<label for="commune" class="<?php echo $class_label; ?> col-form-label">Commune</label>
						<div class="<?php echo $class_champ; ?>">
							<select name="commune" class="chosen-select" required="required" data-placeholder="">
								<?php if (sizeof($communes) > 1) { ?>
									<option value="">-</option>
								<?php } ?>
								<?php

								foreach ($communes as $d) {
									$cle = $d['id'] . '|' . $d['codePostal'] . '|' . $d['nom'] . '|' . $d['code'];
									echo '<option value="' . htmlentities($cle) . '"';
									if (@$post['commune'] == $cle) echo ' selected="selected"';
									echo '>';
									echo $d['nom'];
									//if ( isset($_GET['devise']) && $_GET['devise'] == 'CHF' ) echo ' - ' . $d['complement'] ;
									echo ' - ' . $d['codePostal'];
									if (isset($d['complement']) && $d['complement'] != '') echo ' (' . $d['complement'] . ')';
									echo '</option>';
								}

								?>
							</select>
						</div>
					</div>

					<div class="alert alert-info" role="alert">
						<p>Saisir le lieu précis où se déroule l’événement <strong>seulement si nécessaire</strong> (si l'adresse n'est pas suffisante).<br />
							Ex : Espace culturel / Place du village / Salle des fêtes / Esplanade du lac...</p>
					</div>

					<div class="form-group row">
						<label for="lieu" class="<?php echo $class_label; ?> col-form-label">Lieu précis</label>
						<div class="<?php echo $class_champ; ?>">
							<input class="form-control" type="text" name="lieu" value="<?php echo htmlentities(@$post['lieu']); ?>" id="lieu">
						</div>
					</div>

				</fieldset>

				<fieldset class="form-group">

					<legend>Dates de la manifestation</legend>

					<div class="alert alert-warning" role="alert">
						Merci de préciser au minimum une date.
					</div>

					<?php if ( @$multiHoraire )
						{
							include(realpath(dirname(__FILE__)).'/form.multihoraire.inc.php') ;
						} else {
					?>

					<div class="table-responsive">
						<table class="table dates">
							<thead>
								<tr>
									<th></th>
									<th class="required">Début</th>
									<th class="required">Fin</th>
									<th>Heure de début</th>
									<th>Heure de fin</th>
									<th>Complément</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$nb = 1;
								if (isset($post['date'])) $nb = sizeof($post['date']);
								for ($i = 0; $i < $nb; $i++) {
									echo "\n\t\t\t\t\t\t" . '<tr>';
									echo '<td></td>';
									echo '<td>';
									echo '<div class="input-group form-group date">';
									echo '<input class="form-control date" type="date" min="' . date('Y-m-d') . '" name="date[' . $i . '][debut]" value="' . htmlentities(@$post['date'][$i]['debut']) . '" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="input-group form-group date">';
									echo '<input class="form-control date" type="date" min="' . date('Y-m-d') . '" name="date[' . $i . '][fin]" value="' . htmlentities(@$post['date'][$i]['fin']) . '" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="input-group form-group time">';
									echo '<input class="form-control time" type="time" name="date[' . $i . '][hdebut]" value="' . htmlentities(@$post['date'][$i]['hdebut']) . '" placeholder="hh:mm" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="input-group form-group time">';
									echo '<input class="form-control time" type="time" name="date[' . $i . '][hfin]" value="' . htmlentities(@$post['date'][$i]['hfin']) . '" placeholder="hh:mm" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<input class="form-control" type="text" name="date[' . $i . '][complementHoraire]" value="' . htmlentities(@$post['date'][$i]['complementHoraire']) . '" />';
									echo '</td>';
									echo '</tr>';
								}
								echo '<tr>';
								echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', 'Ajouter une date', $icon_plus) . '</td>';
								echo '</tr>';
								?>
							</tbody>
						</table>
					</div>
					<?php } ?>
				</fieldset>

				<fieldset class="form-group">

					<legend>Description de votre manifestation</legend>

					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label">Type de manifestation</label>
						<div class="<?php echo $class_champ; ?>">
							<?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationType', array('presentation' => 'select', 'type' => 'unique'), @$post['FeteEtManifestationType']); ?>
						</div>
					</div>

					<?php $limitCategories = 3 ; ?>
					<?php if ( isset($_GET['limitCategories']) && is_int($limitCategories) ) $limitCategories = (int)$_GET['limitCategories'] ; ?>
					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label">Catégorie<?php $limitCategories > 1 ? 's':'' ; ?> de manifestation</label>
						<div class="<?php echo $class_champ; ?>">
							<?php if ( $limitCategories > 1 ) { ?>
								<?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationCategorie', array('presentation' => 'select', 'max_selected_options' => $limitCategories, 'exclude' => $categorie_exclude), @$post['FeteEtManifestationCategorie']); ?>
								<small class="form-text text-muted"><?php echo $limitCategories ; ?> catégories maximum</small>
							<?php } else  { ?>
								<?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationCategorie', array('presentation' => 'select', 'type' => 'unique', 'exclude' => $categorie_exclude), @$post['FeteEtManifestationCategorie']); ?>
							<?php } ?>
						</div>
					</div>

					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label">Thèmes de manifestation</label>
						<div class="<?php echo $class_champ; ?>">
							<?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationTheme', array('presentation' => 'select', 'exclude' => $theme_exclude), @$post['FeteEtManifestationTheme']); ?>
						</div>
					</div>

					<?php if (isset($_GET['generique'])) { ?>
						<?php
						// var t = [] ; jQuery('tr.selected').find('span.label').each(function(){t.push(jQuery(this).text())}) ; t.join(',') ;
						$params_generique = [
							'presentation' => 'select',
							'type' => 'unique',
							'include' => [5948, 2392, 5134, 6501, 3726, 2396, 2412, 4963, 4967, 4964, 4965, 4966, 4565, 2421, 6329, 3911, 2384, 3721, 2386, 5627, 2399, 4145, 2397, 6497, 2429, 2383, 4655, 3756, 5490, 5885, 4052, 2385, 2405, 2395, 6500, 2428, 2425, 4997, 4856, 2427, 4998, 5046, 2406, 2387, 2422, 5945, 2403, 2388, 4047, 2423, 4051, 4913, 4146, 4525, 5860, 6457, 2414, 2398, 5321, 6280, 5380, 2401, 2402, 4070, 4574, 2408, 5745, 2503, 4636, 4656, 2426, 2404, 2424, 2411, 2415, 2400, 4572, 2394, 2391, 2389, 2390, 4654, 2407]
						];
						?>
						<div class="form-group row">
							<label class="<?php echo $class_label; ?> col-form-label">Evénements génériques et championnats</label>
							<div class="<?php echo $class_champ; ?>">
								<?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationGenerique', $params_generique, @$post['FeteEtManifestationGenerique']); ?>
							</div>
						</div>
					<?php } ?>

					<div class="form-group row required">
						<label class="<?php echo $class_label; ?> col-form-label" for="descriptifCourt">Descriptif court
							<i class="fas fa-info-circle" title="Texte d'accroche permettant de comprendre la nature de votre prestation. Ne doit pas contenir d'horaire, de tarif, d'info de réservation, de N° de tél, de lieu... puisque ces informations existent par ailleurs, ce qui constitue une double saisie."></i>
							<br /><small class="form-text text-muted">255 caractères max.</small>
						</label>
						<div class="<?php echo $class_champ; ?>">
							<textarea class="form-control" name="descriptifCourt" id="descriptifCourt" maxlength="255" required="required"><?php echo htmlspecialchars(@$post['descriptifCourt']); ?></textarea>
						</div>
					</div>

					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label" for="descriptifDetaille">Descriptif détaillé
							<i class="fas fa-info-circle" title="Le descriptif détaillé est complémentaire du descriptif court et non redondant. En effet certains sites web affichent ces deux champs à la suite."></i>
						</label>
						<div class="<?php echo $class_champ; ?>">
							<textarea class="form-control" name="descriptifDetaille" id="descriptifDetaille"><?php echo htmlspecialchars(@$post['descriptifDetaille']); ?></textarea>
						</div>
					</div>

					<?php
					if (isset($_GET['clientele'])) {
						$labelClientele = 'Types de Clientèle';
						$params = array(
							'presentation' => 'select',
							//'include' => Array(6486) // Pass sanitaire obligatoire
							/*
								 	3734 Spécial LGBT	10.02.72	Offres adaptées à des clientèles spécifiques		6	
									3737 Réservé à un public majeur	10.02.75	Offres adaptées à des clientèles spécifiques		41	
									579 Spécial célibataires	10.02.54	Offres adaptées à des clientèles spécifiques		42	
									594 Spécial enfants	10.02.49	Offres adaptées à des clientèles spécifiques		43	
									4908 Spécial étudiants		Offres adaptées à des clientèles spécifiques		44	
									4813 Spécial retraités		Offres adaptées à des clientèles spécifiques		45	
									5416 Spécial sportifs		Offres adaptées à des clientèles spécifiques		46	
									496 Spécial adolescents	10.02.01	Offres adaptées à des clientèles spécifiques		47	
									509 Spécial amoureux	10.02.08	Offres adaptées à des clientèles spécifiques		48	
									513 Spécial famille avec enfants 	10.02.11	Offres adaptées à des clientèles spécifiques
									504 Cavaliers	10.02.06	Clientèles pratiquant une activité spécifique		3	
									511 Curistes	10.02.09	Clientèles pratiquant une activité spécifique		4	
									512 Cyclistes	10.02.10	Clientèles pratiquant une activité spécifique		5	
									565 Motards	10.02.30	Clientèles pratiquant une activité spécifique		7	
									591 Naturistes	10.02.61	Clientèles pratiquant une activité spécifique		8	
									566 Pêcheurs	10.02.31	Clientèles pratiquant une activité spécifique		9	
									522 Pèlerins	10.02.20	Clientèles pratiquant une activité spécifique
									564 Pratiquants de sports d'eaux vives	10.02.29	Clientèles pratiquant une activité spécifique		22	
									558 Randonneurs	10.02.23	Clientèles pratiquant une activité spécifique		23	
									4668 Randonneurs à raquettes acceptés		Clientèles pratiquant une activité spécifique		24	
									563 VTTistes	10.02.28	Clientèles pratiquant une activité spécifique	
	  							*/
							'include' => [
								3734, 3737, 579, 594, 4908, 4813, 5416, 496, 509, 513, 504, 511, 512, 565, 591, 566, 522, 564, 558, 4668, 563
							]
						);
						?>
						<div class="form-group row prestations-typesClientele">
							<label class="<?php echo $class_label; ?> col-form-label"><?php echo $labelClientele; ?></label>
							<div class="<?php echo $class_champ; ?>">
								<?php echo $apidaeEvent->formHtmlCC('TypeClientele', $params, @$post['TypeClientele']); ?>
							</div>
						</div>
						<?php
					}
					?>

					<?php if (isset($_GET['TourismeAdapte']) && $_GET['TourismeAdapte'] == 1) { ?>

						<?php
						// https://apidae-tourisme.zendesk.com/agent/tickets/5997
						$params = array(
							'presentation' => 'checkbox',
							'include' => array(3651, 3653, 3652, 3674, 3675, 3943, 3676, 1191, 1196, 4217, 3666, 1199, 4219)
						);
						?>
						<div class="form-group row TourismeAdapte">
							<label class="<?php echo $class_label; ?> col-form-label">
								Accessibilité
								<br /><small class="form-text text-muted">Accueil des personnes en situation de handicap</small>
							</label>
							<div class="<?php echo $class_champ; ?>">
								<?php echo $apidaeEvent->formHtmlCC('TourismeAdapte', $params, @$post['TourismeAdapte']); ?>
							</div>
						</div>

					<?php } ?>

				</fieldset>

				<fieldset class="form-group">

					<legend>Moyens de communication</legend>

					<div class="alert alert-warning" role="alert">
						Merci de préciser au moins un moyen de communication (Mail, téléphone...) : ils seront diffusés sur les supports de communications (sites web, brochures...)
					</div>

					<div class="table-responsive">
						<table class="table mc">
							<thead>
								<tr>
									<th></th>
									<th class="required">Type</th>
									<th class="required">Coordonnée</th>
									<th>Complément</th>
								</tr>
							</thead>
							<tbody>
								<?php

								$types = $apidaeEvent->getElementsReferenceByType('MoyenCommunicationType', array('include' => $configApidaeEvent['types_mcs']));

								$nb = 3;
								if (isset($post['mc'])) $nb = sizeof($post['mc']);

								for ($i = 0; $i < $nb; $i++) {
									echo "\n\t\t\t\t\t\t" . '<tr>';
									echo '<td></td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<select class="form-control" name="mc[' . $i . '][type]"';
									if ($i == 0) echo ' required="required" ';
									echo '>';
									echo '<option value="">-</option>';
									foreach ($types as $type) {
										echo '<option value="' . $type['id'] . '"';
										if (isset($post['mc'])) {
											if (@$post['mc'][$i]['type'] == $type['id'])
												echo ' selected="selected';
										} else {
											if (
												($i == 0 && $type['id'] == 201) // Téléphone
												|| ($i == 1 && $type['id'] == 204) // Mél
												|| ($i == 2 && $type['id'] == 205) // Site web
											)
												echo ' selected="selected" ';
										}
										echo '>';
										echo $type['libelleFr'];
										echo '</option>';
									}
									echo '</select>';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control" type="text" name="mc[' . $i . '][coordonnee]" value="' . htmlentities(@$post['mc'][$i]['coordonnee']) . '" ';
									if ($i == 0) echo 'required="required" ';
									echo '/>';
									echo '<small style="display:none;" class="help h205">http(s)://...</small>';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control" type="text" name="mc[' . $i . '][observations]" value="' . htmlentities(@$post['mc'][$i]['observations']) . '" />';
									echo '</div>';
									echo '</td>';
									echo '</tr>';
								}
								echo '<tr>';
								echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', 'Ajouter une ligne', $icon_plus) . '</td>';
								echo '</tr>';
								?>
							</tbody>
						</table>
					</div>
				</fieldset>

				<?php if (isset($_GET['reservation']) && $_GET['reservation']) { ?>

					<fieldset>

						<legend>Réservation</legend>

						<div class="form-group row">
							<label for="reservation_nom" class="<?php echo $class_label; ?> col-form-label">Nom de l'organisme</label>
							<div class="<?php echo $class_champ; ?>">
								<input class="form-control" type="text" name="reservation[nom]" id="reservation_nom" value="<?php echo htmlentities(@$post['reservation']['nom']); ?>">
							</div>
						</div>

						<div class="form-group row">
							<label for="reservation_url" class="<?php echo $class_label; ?> col-form-label">URL de réservation<br /><small>http(s)://...</small></label>
							<div class="<?php echo $class_champ; ?>">
								<input class="form-control url" type="text" name="reservation[url]" id="reservation_url" value="<?php echo htmlentities(@$post['reservation']); ?>" placeholder="https://...">
								<small class="helper url">http(s)://...</small>
							</div>
						</div>

					</fieldset>

				<?php } ?>

				<fieldset class="contacts<?php if (isset($_GET['contactObligatoire']) && $_GET['contactObligatoire']) echo ' required'; ?>">

					<legend>Contacts organisateurs</legend>

					<div class="alert alert-warning" role="alert">
						<strong>Merci de préciser au moins une adresse mail (de préférence) et/ou un numéro de téléphone</strong> : en cas de questions, nous pourrons prendre contact avec l'organisateur grâce à ces informations.
					</div>

					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th></th>
									<th>Fonction</th>
									<th>Prénom</th>
									<th>Nom</th>
									<th>Mail</th>
									<th>Téléphone</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$types = $apidaeEvent->getElementsReferenceByType('ContactFonction');
								for ($i = 0; $i < 1; $i++) {
									echo "\n\t\t\t\t\t\t" . '<tr>';
									echo '<td></td>';
									echo '<td>';
									echo '<select class="form-control" name="contact[' . $i . '][fonction]">';
									echo '<option value="">-</option>';
									foreach ($types as $type) {
										echo '<option value="' . $type['id'] . '"';
										if (@$post['contact'][$i]['fonction'] == $type['id']) echo ' selected="selected" ';
										echo '>';
										echo $type['libelleFr'];
										echo '</option>';
									}
									echo '</select>';
									echo '</td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control" type="text" name="contact[' . $i . '][prenom]" value="' . htmlspecialchars(@$post['contact'][$i]['prenom']) . '" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control col" type="text" name="contact[' . $i . '][nom]" value="' . htmlspecialchars(@$post['contact'][$i]['nom']) . '" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control mail" type="text" name="contact[' . $i . '][mail]" value="' . htmlspecialchars(@$post['contact'][$i]['mail']) . '" placeholder="xxx@yyyy.zz" />';
									echo '</div>';
									echo '</td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control telephone" type="text" name="contact[' . $i . '][telephone]" value="' . htmlspecialchars(@$post['contact'][$i]['telephone']) . '" placeholder="' . $phone_placeholder . '" />';
									echo '</div>';
									echo '</td>';
									echo '</tr>';
								}
								echo '<tr>';
								echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', 'Ajouter un contact', $icon_plus) . '</td>';
								echo '</tr>';
								?>
							</tbody>
						</table>
					</div>

				</fieldset>

				<fieldset class="form-group">
					<legend>Tarifs</legend>

					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label" for="gratuit">Gratuit pour les visiteurs</label>
						<div class="<?php echo $class_champ; ?>">
							<input type="checkbox" name="gratuit" id="gratuit" value="1" <?php if (@$post['gratuit'] == 1) echo ' checked="checked" '; ?> />
						</div>
					</div>

					<div class="champ tarifs">
						<div class="block">

							<div class="alert alert-warning" role="alert">
								<p><strong>Attention</strong> : chaque type de tarif n'est utilisable qu'une fois. Si vous avez plusieurs "pleins tarifs", précisez la plage mini-maxi sur une seule ligne.</p>
							</div>

							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th></th>
											<th>Type de tarif</th>
											<th>Mini <?php echo $devise_lib; ?> (à partir de...)</th>
											<th>Maxi <?php echo $devise_lib; ?> (jusqu'à...)</th>
											<th>Précisions tarifs</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<td colspan="5"></td>
										</tr>
									</tfoot>
									<tbody>
										<?php
										$types = $apidaeEvent->getElementsReferenceByType('TarifType', array('include' => $configApidaeEvent['types_tarifs']));
										for ($i = 0; $i < 1; $i++) {
											echo "\n\t\t\t\t\t\t" . '<tr>';
											echo '<td></td>';
											echo '<td>';
											/*
													TODO si on veut permettre le choix des tarifs à l'internaute : penser à dupliquer le tarif ci-dessous lors de l'ajout d'une ligne de tarif en javascript, et traiter dans index.post.php la devise tarif par tarif.
													$devise_tarif = ( isset($post['tarifs'][$i]['devise']) ) ? $post['tarifs'][$i]['devise'] : $devise_apidae ;
													echo '<input type="hidden" name="tarifs['.$i.'][devise]" value="'.htmlspecialchars($devise_tarif).'" />' ;
													*/
											echo '<div class="form-group">';
											echo '<select class="form-control" name="tarifs[' . $i . '][type]">';
											echo '<option value="">-</option>';
											foreach ($types as $type) {
												echo '<option value="' . $type['id'] . '"';
												if (@$post['tarifs'][$i]['type'] == $type['id']) echo ' selected="selected" ';
												echo '>';
												echo $type['libelleFr'];
												echo '</option>';
											}
											echo '</select>';
											echo '</div>';
											echo '</td>';
											echo '<td>';
											echo '<div class="input-group form-group mb-2 mr-sm-2 mb-sm-0">';
											echo '<input class="form-control float" type="text" name="tarifs[' . $i . '][mini]" value="' . htmlspecialchars(@$post['tarifs'][$i]['mini']) . '" />';
											echo '<div class="input-group-addon">' . $devise_lib . '</div>';
											echo '</div>';
											echo '</td>';
											echo '<td>';
											echo '<div class="input-group form-group mb-2 mr-sm-2 mb-sm-0">';
											echo '<input class="form-control float" type="text" name="tarifs[' . $i . '][maxi]" value="' . htmlspecialchars(@$post['tarifs'][$i]['maxi']) . '" />';
											echo '<div class="input-group-addon">' . $devise_lib . '</div>';
											echo '</div>';
											echo '</td>';
											echo '<td><input class="form-control" type="text" name="tarifs[' . $i . '][precisions]" value="' . htmlspecialchars(@$post['tarifs'][$i]['precisions']) . '" /></td>';
											echo '</tr>';
										}
										echo '<tr>';
										echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', 'Ajouter un tarif', $icon_plus) . '</td>';
										echo '</tr>';
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="form-group row complement_tarif">
						<label class="<?php echo $class_label; ?> col-form-label" for="descriptionTarif_complement_libelleFr">Complément sur les tarifs</label>
						<div class="<?php echo $class_champ; ?>">
							<textarea class="form-control" name="descriptionTarif_complement_libelleFr" id="descriptionTarif_complement_libelleFr"><?php echo htmlspecialchars(@$post['descriptionTarif_complement_libelleFr']); ?></textarea>
						</div>
					</div>

					<?php
					$params_paiement = array(
						'presentation' => 'checkbox',
						'exclude' => array(
							1265, // American Express
							1266, // Bons CAF
							//1268, // Carte bancaire/crédit
							1269, // Carte JCB
							1286, // Pass’Région
							//1271, // Chèque
							4136, // Chèque cadeau Gîtes de France
							4139, // Chèques cadeaux
							1284, // Chèque Culture
							1273, // Chèque de voyage
							5646, // Chéquier Jeunes
							//1274, // Chèque Vacances
							1275, // Devise étrangère
							1276, // Diners Club
							//1277, // Espèces
							4098, // Moneo resto
							5408, // Monnaie locale
							5558, // Paiement en ligne
							1287, // Paypal
							1285, // Titre Restaurant
							1281, // Virement
						)
					);
					?>
					<div class="form-group row modes_paiement">
						<label class="<?php echo $class_label; ?> col-form-label">Modes de paiement</label>
						<div class="<?php echo $class_champ; ?>">
							<?php echo $apidaeEvent->formHtmlCC('ModePaiement', $params_paiement, @$post['ModePaiement']); ?>
						</div>
					</div>

				</fieldset>

				<?php
				$classes = ['form-group', 'illustrations'];
				if (isset($_GET['illustrationObligatoire']) && $_GET['illustrationObligatoire']) $classes[] = 'required';
				if (isset($_GET['copyright']) && $_GET['copyright']) $classes[] = 'copyright';
				?>
				<fieldset class="<?php echo implode(' ', $classes); ?>">
					<legend>Photos</legend>
					<div class="alert alert-warning" role="alert">
						Vos photos doivent être libres de droit et de bonne qualité
						<?php if (isset($_GET['illustrationMini'])) { ?>
							(<strong><?php echo $_GET['illustrationMini']; ?>px de largeur minimum</strong>).
						<?php } else { ?>
							(<strong>si possible, 1200px de largeur minimum</strong>).
						<?php } ?>
						<br />Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...) : <strong>assurez-vous d'avoir tous les droits nécessaires</strong>, et précisez le Copyright si besoin.
						<br />
						<a href="https://aide.apidae-tourisme.com/hc/fr/articles/360000825391-Saisie-l-onglet-multimédias-Zoom-sur-les-illustrations#tailleimages" target="_blank"><i class="fas fa-info-circle"></i> Plus d'informations ici.</a>
					</div>
					<div class="table-responsive">
						<table class="table photos">
							<thead>
								<tr>
									<th></th>
									<th>Votre photo</th>
									<th>Titre</th>
									<th>Copyright</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="4"></td>
								</tr>
							</tfoot>
							<tbody>
								<?php
								for ($i = 0; $i < 1; $i++) {
									echo "\n\t\t\t\t\t\t" . '<tr>';
									echo '<td></td>';
									echo '<td>';
									echo '<input class="form-control" type="file" name="illustrations[' . $i . ']" accept="image/*" ';
									if (isset($_GET['illustrationMini']) && (int)$_GET['illustrationMini'] > 0 && (int)$_GET['illustrationMini'] <= 2000)
										echo 'minwidth="' . (int)$_GET['illustrationMini'] . '" ';
									echo '/>';
									echo '</td>';
									echo '<td><input class="form-control" type="text" name="illustrations[' . $i . '][legende]" value="' . htmlspecialchars(@$post['illustrations'][$i]['legende']) . '" /></td>';
									echo '<td>';
									echo '<div class="form-group">';
									echo '<input class="form-control" type="text" name="illustrations[' . $i . '][copyright]" value="' . htmlspecialchars(@$post['illustrations'][$i]['copyright']) . '" />';
									echo '</div>';
									echo '</td>';
									echo '</tr>';
								}
								?>
								<tr>
									<td class="plus" colspan="99"><?php echo preg_replace('/##LIBELLE##/', 'Ajouter une photo', $icon_plus); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</fieldset>

				<?php if (isset($_GET['mm']) && $_GET['mm'] == 1) { ?>
					<fieldset class="form-group">
						<legend>Multimédias</legend>
						<div class="alert alert-warning" role="alert">
							Vous pouvez ajouter ci-dessous des fichiers PDF si nécessaire (si vous avez un programme par exemple).
							<br />Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...) : <strong>assurez-vous d'avoir tous les droits nécessaires</strong>, et précisez le Copyright si besoin.
							<br />Les documents ajoutés ne doivent pas dépasser les 5 Mo au total.
						</div>
						<div class="table-responsive">
							<table class="table photos">
								<thead>
									<tr>
										<th></th>
										<th>Votre fichier</th>
										<th>Titre</th>
										<th>Copyright</th>
									</tr>
								</thead>
								<tbody>
									<?php
									for ($i = 0; $i < 1; $i++) {
										echo "\n\t\t\t\t\t\t" . '<tr>';
										echo '<td></td>';
										echo '<td>';
										echo '<input class="form-control" type="file" name="multimedias[' . $i . ']" value="' . htmlspecialchars(@$post['multimedias'][$i]) . '" accept="' . implode(',', $configApidaeEvent['mimes_multimedias']) . '" />';
										echo '</td>';
										echo '<td><input class="form-control" type="text" name="multimedias[' . $i . '][legende]" value="' . htmlspecialchars(@$post['multimedias'][$i]['legende']) . '" /></td>';
										echo '<td><input class="form-control" type="text" name="multimedias[' . $i . '][copyright]" value="' . htmlspecialchars(@$post['multimedias'][$i]['copyright']) . '" /></td>';
										echo '</tr>';
									}
									?>
									<tr>
										<td class="plus" colspan="99"><?php echo preg_replace('/##LIBELLE##/', 'Ajouter un fichier', $icon_plus); ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</fieldset>
				<?php } ?>

				<fieldset class="form-group">
					<legend>Organisateur</legend>
					<div class="alert alert-info" role="alert">
						Vous pouvez laisser un message ci-dessous : il sera communiqué à votre office de tourisme, mais ne sera pas publié.<br />
						Merci de préciser <strong>l'organisateur de la manifestation</strong> (association ABC...).
					</div>
					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label" for="commentaire">Commentaire privé</label>
						<div class="<?php echo $class_champ; ?>">
							<textarea class="form-control" name="commentaire" id="commentaire"><?php echo htmlspecialchars(@$post['commentaire']); ?></textarea>
						</div>
					</div>
				</fieldset>

				<?php if ($configApidaeEvent['debug']) { ?>
					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label" for="nosave">[Debug] Ne pas enregistrer sur Apidae</label>
						<div class="<?php echo $class_champ; ?>">
							<input type="checkbox" name="nosave" id="nosave" value="1" <?php if (@$post['nosave'] == 1) echo ' checked="checked" '; ?> />
						</div>
					</div>
					<div class="form-group row">
						<label class="<?php echo $class_label; ?> col-form-label" for="nomail">[Debug] Ne pas envoyer les mails (même pas à admin)</label>
						<div class="<?php echo $class_champ; ?>">
							<input type="checkbox" name="nomail" id="nomail" value="1" <?php if (@$post['nomail'] == 1) echo ' checked="checked" '; ?> />
						</div>
					</div>
				<?php } ?>

				<input type="hidden" name="script_uri" value="<?php echo htmlentities(@$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI']); ?>" />

				<div class="form-group" <?php
										if (@$configApidaeEvent['recaptcha_secret'] != '' && !$configApidaeEvent['debug']) echo ' style="display:none;"';
										?>>
					<input type="button" class="btn btn-success btn-lg btn-block btn-submit" value="Enregistrer cet événement" />
				</div>

				<?php if (@$configApidaeEvent['recaptcha_secret'] != '' && !$configApidaeEvent['debug']) { ?>
					<div class="form-group" id="recaptcha">
						<div class="g-recaptcha" data-sitekey="<?php echo $configApidaeEvent['recaptcha_sitekey']; ?>" data-callback="recaptchaOk" data-expired-callback="recaptchaKo"></div>
						<p>Vous devez cocher la case "Je ne suis pas un robot" pour pouvoir enregistrer</p>
					</div>
				<?php } ?>

				<div style="text-align:center;padding:40px ;">
					<?php if ( strtotime(date('Y-m-d')) < strtotime('2022-07-05') ) { ?>
					<a href="https://www.apidae-tourisme.com" target="_blank"><img src="./logo.png" alt="Apidae Event" width="170" /></a>
					<?php } else { ?>
						<a href="https://www.apidae-tourisme.com" target="_blank"><img src="./Apidae_Event.png" alt="Apidae Event" width="170" /></a>
					<?php } ?>
				</div>

			</form>

		<?php } ?>

	</div>

</body>

</html>