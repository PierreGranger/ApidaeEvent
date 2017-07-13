<?php

	require_once(realpath(dirname(__FILE__)).'/requires.inc.php') ;

	$class_label = 'col-md-2 col-sm-2' ;
	$class_champ = 'col-md-10 col-sm-10' ;

?><!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<!-- jQuery -->
		<script src="./js/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>

		<!-- Bootstrap -->
		<link href="./css/bootstrap.min.css" rel="stylesheet">
		<script src="./js/bootstrap.min.js"></script>

		<!-- Chosen -->
		<script src="./chosen/chosen.jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="./chosen/chosen.min.css" media="all" />
		<link rel="stylesheet" type="text/css" href="./css/bootstrap.chosen.css" media="all" />

		<!-- jQuery.ui -->
		<script
		src="./js/jquery-ui.min.js"></script>
		<script src="./js/datepicker-fr.js"></script>
		<link rel="stylesheet" type="text/css" href="./css/jquery-ui.css" media="all" />

		<!-- jQuery timepicker -->
		<script src="./js/jquery.timepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="./css/jquery.timepicker.min.css" media="all" />

		<?php
			$icon_plus = '<span class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> <strong>Ajouter une ligne</strong></span>' ;
			$icon_moins = '<span class="btn btn-warning"><span class="glyphicon glyphicon-minus"></span></span>' ;
		?>
		<script>
			var icon_plus = '<?php echo $icon_plus ; ?>' ;
			var icon_moins = '<?php echo $icon_moins ; ?>' ;
		</script>
		<script src="./js/formulaire.js?v=3"></script>
		<link rel="stylesheet" type="text/css" href="./css/formulaire.css" media="all" />

		<?php include(realpath(dirname(__FILE__)).'/../analytics.php') ; ?>

		<script src='https://www.google.com/recaptcha/api.js'></script>

		<?php
			if ( isset($_GET['theme']) && preg_match('#^[a-z0-9]+$#',$_GET['theme']) )
				echo '<link rel="stylesheet" type="text/css" href="./css/theme.'.$_GET['theme'].'.css" media="all" />' ;
		?>
		
		<?php if ( false ) { ?>

		<link rel="stylesheet" type="text/css" href="./css/form2015.css" media="all" />
		<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=AIzaSyCM3vctoV2OclI__GzcHFel1eJw06OU8as&callback=initAutocomplete"></script>
		<script src="./ressources/dropzone.js"></script>

		<!-- Moment -->
		<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/locale/fr.js"></script>
		
		<!-- Bootstrap DateTimepicker https://eonasdan.github.io/bootstrap-datetimepicker/ -->
		<script src="./js/bootstrap-datetimepicker.js"></script>

		<!-- Form validator -->
		<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.js"></script>
		<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/additional-methods.js"></script>

		<?php } ?>

	</head>
	<body>

		<div class="container">

			<?php

				$ko = Array() ;
				$ok = Array() ;

				$display_form = true ;

				if ( isset($_POST['nom']) )
				{
					include(realpath(dirname(__FILE__)).'/index.post.php') ;
				}

				$post = $_POST ;
				if ( ! is_array($post) ) $post = Array() ;

			?>


			<?php if ( $display_form ) { ?>

			<?php

				if ( $_config['debug'] )
				{
					?>
					<div class="alert alert-success" role="alert">
						<h3>[DEBUG] Membres sur lesquels on peut saisir :</h3>
						<?php
							echo '<ul>' ;
							foreach ( $_config['membres'] as $membre )
							{
								echo '<li>' ;
									echo '<a href="'.$pma->url_base().'/echanger/membre-sitra/'.$membre['id_membre'].'" target="_blank">'.$membre['nom'].'</a> ' ;
									if ( $membre['id_territoire'] !== null )
										echo ' (territoire : <a href="'.$pma->url_base().'/consulter/objet-touristique/'.$membre['id_territoire'].'" target="_blank">'.$membre['id_territoire'].'</a>) ' ;
									else
										echo '<strong style="color:red;">Non renseignée</strong>' ;
									echo ' - ' ;
									echo 'API écriture : ' ;
									if ( @$membre['clientId'] !== null ) echo 'Renseignée' ;
									else echo '<strong style="color:red;">Non renseignée</strong>' ;
								echo '</li>' ;
							}
							echo '</ul>' ;
						?>
					</div>
					<div class="alert alert-info" role="alert">
						<h3>[DEBUG] Remontée des bugs et évolutions :</h3>
						<a href="https://docs.google.com/spreadsheets/u/0/d/1wSidT7V26kem9jyewHdN-KbfGAj8WaPTq8KAR50HUko/edit" target="_blank">https://docs.google.com/spreadsheets/u/0/d/1wSidT7V26kem9jyewHdN-KbfGAj8WaPTq8KAR50HUko/edit</a>
					</div>
					<?php if ( @$_config['projet_ecriture_multimembre'] == 1 ) { ?>
					<div class="alert alert-success" role="alert">
						<h3>[DEBUG] API écriture multimembre ON !!</h3>
					</div>
					<?php }
				}

			?>

			<form class="form" method="post" enctype="multipart/form-data" novalidate>

				<?php $referer = ( isset($_POST['referer']) ) ? $_POST['referer'] : @$_SERVER['HTTP_REFERER'] ; ?>
				<input type="hidden" name="referer" value="<?php echo htmlentities($referer) ; ?>" />
				<input type="hidden" name="script_uri" value="<?php echo htmlentities(@$_SERVER['HTTP_HOST'].@$_SERVER['REQUEST_URI']) ; ?>" />

				<fieldset class="form-group required">
					<legend>Nom de la manifestation</legend>
					<div class="controls">
						<input class="form-control form-control-lg" name="nom" type="text" value="<?php echo htmlentities(@$post['nom']) ; ?>" id="nom" required="required" />
					</div>
				</fieldset>
					
				<fieldset class="form-group">

					<legend>Importance de votre événement</legend>

					<div class="form-group row required">

						<label for="portee" class="<?php echo $class_label ; ?> col-form-label">
							Portée
							<span class="glyphicon glyphicon-info-sign" title="La portée concerne les spectateurs et la distance qu’ils sont prêt à parcourir pour participer à une manifestation."></span>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<select class="form-control" name="portee" id="portee" required="required">
								<option value="">-</option>
								<?php
									
									$FeteEtManifestationPortees = $pma->getElementsReference('FeteEtManifestationPortee') ;
									foreach ( $FeteEtManifestationPortees as $option )
									{
										echo '<option value="'.$option['id'].'"' ;
											if ( isset($option['description']) ) echo ' title="'.htmlspecialchars($option['description']).'" ' ;
											if ( isset($post['portee']) && $post['portee'] == $option['id'] ) echo ' selected="selected"' ;
										echo '>'.$option['libelleFr'].'</option>' ;
									}
									
								?>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="nbParticipantsAttendu" class="<?php echo $class_label ; ?> col-form-label">Participants attendus</label>
						<div class="col-md-4 col-sm-4">
							<input class="form-control" type="number" name="nbParticipantsAttendu" id="nbParticipantsAttendu" value="<?php echo htmlentities(@$post['nbParticipantsAttendu']) ; ?>" />
						</div>
						<label for="nbVisiteursAttendu" class="<?php echo $class_label ; ?> col-form-label">Visiteurs attendus</label>
						<div class="col-md-4 col-sm-4">
							<input class="form-control" type="number" name="nbVisiteursAttendu" id="nbVisiteursAttendu" value="<?php echo htmlentities(@$post['nbVisiteursAttendu']) ; ?>" />
						</div>
					</div>
				</fieldset>
				

				<fieldset class="form-group">
					<legend>Adresse</legend>
					<div class="form-group row">
						<label for="lieu" class="<?php echo $class_label ; ?> col-form-label">Lieu de la manifestation
							<span class="glyphicon glyphicon-info-sign" title="Saisir le lieu précis (ne correspond pas à une adresse postale) où se déroule l’événement (Espace culturel / Place du village / Salle des fêtes / Esplanade du lac...)."></span>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<input class="form-control" type="text" name="lieu" value="<?php echo htmlentities(@$post['lieu']) ; ?>" id="lieu">
						</div>
					</div>
					<div class="form-group row">
						<label for="adresse1" class="<?php echo $class_label ; ?> col-form-label">Adresse 1
							<span class="glyphicon glyphicon-info-sign" title="Voie et bâtiment. Exemple : 60 rue des Lilas – Bâtiment A. Pas de virgule mais un espace entre le numéro et le nom de la rue."></span>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<input class="form-control" type="text" name="adresse1" value="<?php echo htmlentities(@$post['adresse1']) ; ?>" />
						</div>
					</div>
					<div class="form-group row">
						<label for="adresse2" class="<?php echo $class_label ; ?> col-form-label">Adresse 2
							<span class="glyphicon glyphicon-info-sign" title="Lieu-dit, zone d’activité, BP (pour boite postale)…"></span>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<input class="form-control" type="text" name="adresse2" value="<?php echo htmlentities(@$post['adresse2']) ; ?>" />
						</div>
					</div>
					<div class="form-group row">
						<label for="adresse3" class="<?php echo $class_label ; ?> col-form-label">Adresse 3
							<span class="glyphicon glyphicon-info-sign" title="Niveau de la station et/ou le quartier si nécessaire. Exemple : Morillon village et Morillon 1100."></span>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<input class="form-control" type="text" name="adresse3" value="<?php echo htmlentities(@$post['adresse3']) ; ?>" />
						</div>
					</div>
					<?php

						unset($rq) ;
						if ( isset($_config['territoire']) )
						{
							$sql = ' select count(*) as nb from apidae_territoires where id_territoire = "'.$pma->mysqli->real_escape_string($_config['territoire']).'" ' ;
							$rq = $pma->mysqli->query($sql) or die($pma->mysqli->error) ;
							if ( $d = $rq->fetch_assoc() )
							{
								if ( $d['nb'] == 0 )
								{
									$pma->getTerritoires(true,Array($_config['territoire'])) ;
								}
							}

							$sql = ' select distinct C.* from apidae_communes C
							inner join apidae_territoires T on T.id_commune = C.id
							where T.id_territoire = "'.$pma->mysqli->real_escape_string($_config['territoire']).'" ' ;
							$rq = $pma->mysqli->query($sql) or die($pma->mysqli->error) ;
						}
						elseif ( isset($_config['communes']) )
						{
							$sql = ' select distinct * from apidae_communes where code regexp "'.$pma->mysqli->real_escape_string($_config['communes']).'" ' ;
							$rq = $pma->mysqli->query($sql) or die($pma->mysqli->error) ;
						}
						if ( ! isset($rq) || $rq->num_rows == 0 )
						{
							$pma->alerte('Liste communes introuvable',$_GET) ;
							?>
								<div class="alert alert-danger" role="alert">
								  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
								  <strong>Impossible de récupérer la liste de communes...</strong>
								  <br />Veuillez nous excuser pour la gène occasionnée.
								  <br />Vous pouvez prendre contact avec l'<a href="http://www.apidae-tourisme.com/cartographie-des-membres-apidae/cartographie-des-contributeurs/" target="_blank">Office du Tourisme concernée par votre manifestation</a>.
								</div>
							<?php
							die() ;
						}

					?>
					<div class="form-group row required">
						<label for="commune" class="<?php echo $class_label ; ?> col-form-label">Commune</label>
						<div class="<?php echo $class_champ ; ?>">
							<select name="commune" class="chosen" required="required" data-placeholder="">
								<option value="">-</option>
								<?php
									
									while ( $d = $rq->fetch_assoc() )
									{
										$cle = $d['id'].'|'.$d['codePostal'].'|'.$d['nom'] ;
										echo '<option value="'.htmlentities($cle).'"' ;
											if ( @$post['commune'] == $cle ) echo ' selected="selected"' ;
										echo '>' ;
											echo $d['nom'] . ' - ' . $d['codePostal'] ;
										echo '</option>' ;
									}
									
								?>
							</select>
						</div>
					</div>
				</fieldset>

				<fieldset class="form-group">

					<legend>Dates de la manifestation</legend>

					<div class="alert alert-warning" role="alert">
						Merci de préciser au minimum une date.
					</div>

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
							$nb = 1 ;
							if ( isset($post['date']) ) $nb = sizeof($post['date']) ;
							for ( $i = 0 ; $i < $nb ; $i++ )
							{
								echo "\n\t\t\t\t\t\t".'<tr>' ;
									echo '<td></td>' ;
									echo '<td>' ;
										echo '<div class="input-group form-group date">' ;
											echo '<input class="form-control date" type="text" min="'.date('Y-m-d').'" name="date['.$i.'][debut]" value="'.htmlentities(@$post['date'][$i]['debut']).'" placeholder="jj/mm/aaaa" required="required" />' ;
                    						echo '<span class="input-group-addon">' ;
                        						echo '<span class="glyphicon glyphicon-calendar"></span>' ;
                    						echo '</span>' ;
                						echo '</div>' ;
									echo '</td>' ;
									echo '<td>' ;
										echo '<div class="input-group form-group date">' ;
											echo '<input class="form-control date" type="text" min="'.date('Y-m-d').'" name="date['.$i.'][fin]" value="'.htmlentities(@$post['date'][$i]['fin']).'" placeholder="jj/mm/aaaa" required="required" />' ;
                    						echo '<span class="input-group-addon">' ;
                        						echo '<span class="glyphicon glyphicon-calendar"></span>' ;
                    						echo '</span>' ;
                						echo '</div>' ;
									echo '</td>' ;
									echo '<td>' ;
										echo '<div class="input-group form-group time">' ;
											echo '<input class="form-control time" type="text" name="date['.$i.'][hdebut]" value="'.htmlentities(@$post['date'][$i]['hdebut']).'" placeholder="hh:mm" />' ;
                    						echo '<span class="input-group-addon">' ;
                        						echo '<span class="glyphicon glyphicon-time"></span>' ;
                    						echo '</span>' ;
                						echo '</div>' ;
									echo '</td>' ;
									echo '<td>' ;
										echo '<div class="input-group form-group time">' ;
											echo '<input class="form-control time" type="text" name="date['.$i.'][hfin]" value="'.htmlentities(@$post['date'][$i]['hfin']).'" placeholder="hh:mm" />' ;
                    						echo '<span class="input-group-addon">' ;
                        						echo '<span class="glyphicon glyphicon-time"></span>' ;
                    						echo '</span>' ;
                						echo '</div>' ;
									echo '</td>' ;
									echo '<td>' ;
										echo '<input class="form-control" type="text" name="date['.$i.'][complementHoraire]" value="'.htmlentities(@$post['date'][$i]['complementHoraire']).'" />' ;
									echo '</td>' ;
								echo '</tr>' ;
							}
							echo '<tr>' ;
								echo '<td class="plus" colspan="99">'.$icon_plus.'</td>' ;
							echo '</tr>' ;
						?>
						</tbody>
					</table>
				</fieldset>

				<fieldset class="form-group">

					<legend>Description de votre manifestation</legend>

					<div class="form-group row">
						<label class="<?php echo $class_label ; ?> col-form-label">Types de manifestation</label>
						<div class="<?php echo $class_champ ; ?>">
							<?php echo $pma->formHtmlCC('FeteEtManifestationType',Array('presentation'=>'select','type'=>'unique'),@$post['FeteEtManifestationType']) ; ?>
						</div>
					</div>

					<div class="form-group row">
						<label class="<?php echo $class_label ; ?> col-form-label">Catégories de manifestation</label>
						<div class="<?php echo $class_champ ; ?>">
							<?php echo $pma->formHtmlCC('FeteEtManifestationCategorie',Array('presentation'=>'select','max_selected_options'=>3),@$post['FeteEtManifestationCategorie']) ; ?>
								<small class="form-text text-muted">3 catégories maximum</small>
						</div>
					</div>

					<div class="form-group row">
						<label class="<?php echo $class_label ; ?> col-form-label">Thèmes de manifestation</label>
						<div class="<?php echo $class_champ ; ?>">
							<?php echo $pma->formHtmlCC('FeteEtManifestationTheme',Array('presentation'=>'select'),@$post['FeteEtManifestationTheme']) ; ?>
						</div>
					</div>

					<div class="form-group row required">
						<label class="<?php echo $class_label ; ?> col-form-label" for="descriptifCourt">Descriptif court
							<span class="glyphicon glyphicon-info-sign" title="Texte d'accroche permettant de comprendre la nature de votre prestation. Ne doit pas contenir d'horaire, de tarif, d'info de réservation, de N° de tél, de lieu... puisque ces informations existent par ailleurs, ce qui constitue une double saisie."></span>
							<br /><small class="form-text text-muted">255 caractères max.</small>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<textarea class="form-control" name="descriptifCourt" id="descriptifCourt" maxlength="255" required="required"><?php echo htmlspecialchars(@$post['descriptifCourt']) ; ?></textarea>
						</div>
					</div>

					<div class="form-group row">
						<label class="<?php echo $class_label ; ?> col-form-label" for="descriptifDetaille">Descriptif détaillé
							<span class="glyphicon glyphicon-info-sign" title="Le descriptif détaillé est complémentaire du descriptif court et non redondant. En effet certains sites web affichent ces deux champs à la suite."></span>
						</label>
						<div class="<?php echo $class_champ ; ?>">
							<textarea class="form-control" name="descriptifDetaille" id="descriptifDetaille"><?php echo htmlspecialchars(@$post['descriptifDetaille']) ; ?></textarea>
						</div>
					</div>

				</fieldset>

				<fieldset class="form-group">

					<legend>Moyens de communication</legend>

					<div class="alert alert-warning" role="alert">
						Merci de préciser au moins un moyen de communication (Mail, téléphone...) : ils seront diffusés sur les supports de communications (sites web, brochures...)
					</div>

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
							
							$types = $pma->getElementsReference('MoyenCommunicationType') ;
							
							$nb = 3 ;
							if ( isset($post['mc']) ) $nb = sizeof($post['mc']) ;

							for ( $i = 0 ; $i < $nb ; $i++ )
							{
								echo "\n\t\t\t\t\t\t".'<tr>' ;
									echo '<td></td>' ;
									echo '<td>' ;
										echo '<div class="form-group">' ;
											echo '<select class="form-control" name="mc['.$i.'][type]"' ;
												if ( $i == 0 ) echo ' required="required" ' ;
											echo '>' ;
												echo '<option value="">-</option>' ;
												foreach ( $types as $type )
												{
													echo '<option value="'.$type['id'].'"' ;
														if ( isset($post['mc']) )
														{
															if ( @$post['mc'][$i]['type'] == $type['id'] )
																echo ' selected="selected' ;
														}
														else
														{
															if ( 
																( $i == 0 && $type['id'] == 201 ) // Téléphone
																|| ( $i == 1 && $type['id'] == 204 ) // Mél
																|| ( $i == 2 && $type['id'] == 205 ) // Site web
															)
															echo ' selected="selected" ' ;
														}
													echo '>' ;
														echo $type['libelleFr'] ;
													echo '</option>' ;
												}
											echo '</select>' ;
										echo '</div>' ;
									echo '</td>' ;
									echo '<td>' ;
										echo '<div class="form-group">' ;
											echo '<input class="form-control" type="text" name="mc['.$i.'][coordonnee]" value="'.htmlentities(@$post['mc'][$i]['coordonnee']).'" ' ;
												if ( $i == 0 ) echo 'required="required" ' ;
											echo '/>' ;
										echo '</div>' ;
									echo '</td>' ;
									echo '<td>' ;
										echo '<div class="form-group">' ;
											echo '<input class="form-control" type="text" name="mc['.$i.'][observations]" value="'.htmlentities(@$post['mc'][$i]['observations']).'" />' ;
										echo '</div>' ;
									echo '</td>' ;
								echo '</tr>' ;
							}
							echo '<tr>' ;
								echo '<td class="plus" colspan="99">'.$icon_plus.'</td>' ;
							echo '</tr>' ;
						?>
						</tbody>
					</table>
				</fieldset>

				<fieldset>
					
					<legend>Contacts</legend>
					
					<div class="alert alert-warning" role="alert">
						Les contacts nous permettront de vous recontacter si besoin, mais ne seront pas diffusés.
					</div>

					<table class="table">
						<thead>
							<tr>
								<th></th>
								<th>Fonction</th>
								<th>Prénom</th>
								<th>Nom</th>
								<th>Téléphone</th>
								<th>Mail</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$types = $pma->getElementsReference('ContactFonction',false) ;
								for ( $i = 0 ; $i < 1 ; $i++ )
								{
									echo "\n\t\t\t\t\t\t".'<tr>' ;
										echo '<td></td>' ;
										echo '<td>' ;
											echo '<select class="form-control" name="contact['.$i.'][fonction]">' ;
												echo '<option value="">-</option>' ;
												foreach ( $types as $type )
												{
													echo '<option value="'.$type['id'].'"' ;
														if ( @$post['contact'][$i]['fonction'] == $type['id'] ) echo ' selected="selected" ' ;
													echo '>' ;
														echo $type['libelleFr'] ;
													echo '</option>' ;
												}
											echo '</select>' ;
										echo '</td>' ;
										echo '<td>' ;
											echo '<div class="form-group">' ;
												echo '<input class="form-control" type="text" name="contact['.$i.'][prenom]" value="'.htmlspecialchars(@$post['contact'][$i]['prenom']).'" />' ;
											echo '</div>' ;
										echo '</td>' ;
										echo '<td>' ;
											echo '<div class="form-group">' ;
												echo '<input class="form-control col" type="text" name="contact['.$i.'][nom]" value="'.htmlspecialchars(@$post['contact'][$i]['nom']).'" />' ;
											echo '</div>' ;
										echo '</td>' ;
										echo '<td>' ;
											echo '<div class="form-group">' ;
												echo '<input class="form-control telephone" type="text" name="contact['.$i.'][telephone]" value="'.htmlspecialchars(@$post['contact'][$i]['telephone']).'" placeholder="00 00 00 00 00" />' ;
											echo '</div>' ;
										echo '</td>' ;
										echo '<td>' ;
											echo '<div class="form-group">' ;
												echo '<input class="form-control mail" type="text" name="contact['.$i.'][mail]" value="'.htmlspecialchars(@$post['contact'][$i]['mail']).'" placeholder="xxx@yyyy.zz" />' ;
											echo '</div>' ;
										echo '</td>' ;
									echo '</tr>' ;
								}
								echo '<tr>' ;
									echo '<td class="plus" colspan="99">'.$icon_plus.'</td>' ;
								echo '</tr>' ;
							?>
						</tbody>
					</table>

				</fieldset>

				<fieldset class="form-group">
					<legend>Tarifs</legend>
					
					<div class="form-group row">
						<label class="<?php echo $class_label ; ?> col-form-label" for="gratuit">Gratuit pour les visiteurs</label>
						<div class="<?php echo $class_champ ; ?>">
							<input type="checkbox" name="gratuit" id="gratuit" value="1"<?php if ( @$post['gratuit'] == 1 ) echo ' checked="checked" ' ; ?> />
						</div>
					</div>

					<div class="champ tarifs">
						<div class="block">
							<table class="table">
								<thead>
									<tr>
										<th></th>
										<th>Type de tarif</th>
										<th>Mini € (à partir de...)</th>
										<th>Maxi € (jusqu'à...)</th>
										<th>Précisions tarifs</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$types = $pma->getElementsReference('TarifType',false,$_config['types_tarifs']) ;
										for ( $i = 0 ; $i < 1 ; $i++ )
										{
											echo "\n\t\t\t\t\t\t".'<tr>' ;
												echo '<td></td>' ;
												echo '<td>' ;
													echo '<select class="form-control" name="tarifs['.$i.'][type]">' ;
														echo '<option value="">-</option>' ;
														foreach ( $types as $type )
														{
															echo '<option value="'.$type['id'].'"' ;
																if ( @$post['tarifs'][$i]['type'] == $type['id'] ) echo ' selected="selected" ' ;
															echo '>' ;
																echo $type['libelleFr'] ;
															echo '</option>' ;
														}
													echo '</select>' ;
												echo '</td>' ;
												echo '<td>' ;
													echo '<div class="input-group form-group mb-2 mr-sm-2 mb-sm-0">' ;
														echo '<input class="form-control float" type="text" name="tarifs['.$i.'][mini]" value="'.htmlspecialchars(@$post['tarifs'][$i]['mini']).'" />' ;
    													echo '<div class="input-group-addon">€</div>' ;
													echo '</div>' ;
												echo '</td>' ;
												echo '<td>' ;
													echo '<div class="input-group form-group mb-2 mr-sm-2 mb-sm-0">' ;
														echo '<input class="form-control float" type="text" name="tarifs['.$i.'][maxi]" value="'.htmlspecialchars(@$post['tarifs'][$i]['maxi']).'" />' ;
    													echo '<div class="input-group-addon">€</div>' ;
													echo '</div>' ;
												echo '</td>' ;
												echo '<td><input class="form-control" type="text" name="tarifs['.$i.'][precisions]" value="'.htmlspecialchars(@$post['tarifs'][$i]['precisions']).'" /></td>' ;
											echo '</tr>' ;
										}
										echo '<tr>' ;
											echo '<td class="plus" colspan="99">'.$icon_plus.'</td>' ;
										echo '</tr>' ;
									?>
								</tbody>
							</table>
						</div>
					</div>

					<div class="form-group row complement_tarif">
						<label class="<?php echo $class_label ; ?> col-form-label" for="descriptionTarif_complement_libelleFr">Complément sur les tarifs</label>
						<div class="<?php echo $class_champ ; ?>">
							<textarea class="form-control" name="descriptionTarif_complement_libelleFr" id="descriptionTarif_complement_libelleFr"><?php echo htmlspecialchars(@$post['descriptionTarif_complement_libelleFr']) ; ?></textarea>
						</div>
					</div>

				</fieldset>

				<fieldset class="form-group">
					<legend>Photos</legend>
					<div class="alert alert-warning" role="alert">
						Vos photos doivent être libres de droit et de bonne qualité. Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...).
						<br />
						<a href="http://www.apidae-tourisme.com/prestataires-de-loisirs/" target="_blank"><span class="glyphicon glyphicon-info-sign"></span> Plus d'informations ici.</a>
					</div>
					<table class="table photos">
						<thead>
							<tr>
								<th></th>
								<th>Votre photo</th>
								<th>Légende photo</th>
								<th>Copyright</th>
							</tr>
						</thead>
						<tbody>
							<?php
								for ( $i = 0 ; $i < 1 ; $i++ )
								{
									echo "\n\t\t\t\t\t\t".'<tr>' ;
										echo '<td></td>' ;
										echo '<td>' ;

											//echo '<div class="dropzone dz-clickable"><div class="dz-default dz-message"><span>Ajouter une photo (3 Mo Max)</span></div></div>' ;
												echo '<input class="form-control" type="file" name="medias['.$i.']" value="'.htmlspecialchars(@$post['photos'][$i]).'" />' ;
										echo '</td>' ;
										echo '<td><input class="form-control" type="text" name="medias['.$i.'][legende]" value="'.htmlspecialchars(@$post['medias'][$i]['legende']).'" /></td>' ;
										echo '<td><input class="form-control" type="text" name="medias['.$i.'][copyright]" value="'.htmlspecialchars(@$post['medias'][$i]['copyright']).'" /></td>' ;
									echo '</tr>' ;
								}
							?>
							<tr>
								<td class="plus" colspan="99"><?php echo $icon_plus ; ?></td>
							</tr>
						</tbody>
					</table>
				</fieldset>

				<div class="form-group"<?php
					if ( @$_config['recaptcha_secret'] != '' && ! $_config['debug'] ) echo ' style="display:none;"' ;
				?>>
					<input type="button" class="btn btn-success btn-lg btn-block btn-submit" value="Enregistrer cet événement" />
				</div>

				<?php if ( @$_config['recaptcha_secret'] != '' && ! $_config['debug'] ) { ?>
					<div class="form-group" id="recaptcha">
						<div class="g-recaptcha" data-sitekey="<?php echo $_config['recaptcha_sitekey'] ; ?>" data-callback="recaptchaOk"></div>
					<p>Vous devez cocher la case "Je ne suis pas un robot" pour pouvoir enregistrer</p>
					</div>
				<?php } ?>

			</form>

			<?php } ?>

		</div>
		
	</body>

</html>