<?php

use PierreGranger\ApidaeMembres;

if (session_status() == PHP_SESSION_NONE) session_start();

require_once(realpath(dirname(__FILE__)) . '/../../src/requires.inc.php');
require_once(realpath(dirname(__FILE__)) . '/../../vendor/autoload.php');

require(realpath(dirname(__FILE__)).'/auth.inc.php') ;

		$utilisateurApidae = $ApidaeSso->getUserProfile();

		if (!isset($apidaeEvent)) die('no $apidaeEvent');
		$siteweb = null;
		$apidaeMembres = new ApidaeMembres($configApidaeMembres);
		$membre = $apidaeMembres->getMembreById($utilisateurApidae['membre']['id']);
		if (isset($membre['entitesJuridiques'][0]['id'])) {
			$entite = $apidaeEvent->getOffre($membre['entitesJuridiques'][0]['id'],null,isset($_GET['refresh']));
			if (isset($entite['informations']['moyensCommunication'])) {
				foreach ($entite['informations']['moyensCommunication'] as $mc) {
					if ($mc['type']['id'] == 205) {
						$siteweb = $mc['coordonnees']['fr'];
						break;
					}
				}
			}
		}

			?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>

	<style>
		div#url,
		#html {
			font-family: monospace;
		}

		#html {
			width: 100%;
		}
	</style>

</head>

<body>

	<div class="container">

		<div>
			<a href="?logout">Se déconnecter</a>
		</div>

		<h1>ApidaeEvent - aide à la configuration</h1>

		<div class="alert alert-primary">
				<p>Ce générateur vous permet :</p>
				<ul>
					<li>De générer le code d'intégration que vous pourrez ensuite copier/coller</li>
					<li>De préparer les informations à communiquer à event@apidae-tourisme.zendesk.com (Mails, territoire...)</li>
				</ul>
				<p>Il n'enregistre rien :</p>
				<ul>
					<li>Toute modification d'option nécessite de recopier/coller le code d'intégration</li>
					<li>Tout changement d'email à notifier doit être renvoyé à event@apidae-tourisme.zendesk.com</li>
				</ul>
				<p>Aide en ligne : <a href="https://aide.apidae-tourisme.com/hc/fr/articles/360030771712-Apidae-Event-">https://aide.apidae-tourisme.com/hc/fr/articles/360030771712-Apidae-Event-</a></p>
			</div>

		<div class="jumbotron">

			<div class="form">

				<form method="get">

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="territoire">Territoire</label>
						<div class="col-sm-6">
							<select id="territoire" name="territoire" class="select2 js-example-placeholder-single js-states form-control"></select>
							<br />
							<small>Le choix du territoire conditionne la liste des communes proposées dans le formulaire</small>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="contactObligatoire">Contact obligatoire
							<br /><small>1 Mail ou 1 téléphone minimum dans la zone "Contact"</small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="contactObligatoire" name="contactObligatoire" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="TourismeAdapte">Tourisme Adapté
							<br /><small>Champs principaux de "Prestations > Accueil des personnes en situation de handicap > Tourisme adapté"</small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="TourismeAdapte" name="TourismeAdapte" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="clientele">Types de clientèle
							<br /><small>Ajout des champs Types de clientèles (Offres adaptées à des clientèles spécifiques + Clientèle pratiquant une activité spécifique)</small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="clientele" name="clientele" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="generique">Evenements génériques & championnats
							<br /><small>Journées du patrimoine...</small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="generique" name="generique" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="limitCategories">Nombre de catégories
							<br /><small>Défaut : 3</small>
						</label>
						<div class="col-sm-6">
							<input type="number" id="limitCategories" name="limitCategories" value="" placeholder="3" min="1" max="3" step="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="reservation">Réservation
							<br /><small></small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="reservation" name="reservation" value="1" />
						</div>
					</div>


					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="mm">Multimédias
							<br /><small>Permettre l'ajout de multimédias (PDF seulement)</small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="mm" name="mm" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="copyright">Copyright obligatoire
							<br /><small>Si une illustration est ajoutée elle doit avoir un copyright renseigné</small>
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="copyright" name="copyright" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="illustrationMini">Taille mini illustration
							<br /><small>Si une illustration est ajoutée, elle doit obligatoirement faire + de X pixels (ex: 1200)</small>
							<br /><small>Laisser à 0 pour ne pas demander de taille minimale</small>
						</label>
						<div class="col-sm-6">
							<input type="number" id="illustrationMini" name="illustrationMini" value="" min="0" max="2000" step="200" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="illustrationObligatoire">1 illustration obligatoire minimum
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="illustrationObligatoire" name="illustrationObligatoire" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="toutou">Animaux acceptés & descriptif associé
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="toutou" name="toutou" value="u2" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="apihours">Multihoraire ApiHours (popup)
						</label>
						<div class="col-sm-6">
							<input type="checkbox" id="apihours" name="apihours" value="1" />
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="devise">Devise</label>
						<div class="col-sm-6">
							<label><input type="radio" id="devise_eur" name="devise" value="" /> €</label>
							<label><input type="radio" id="devise_chf" name="devise" value="CHF" /> Franc suisse (CHF)</label>
							<label><input type="radio" id="devise_xpf" name="devise" value="XPF" /> Franc pacifique (XPF)</label>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="locale">Langue du formulaire
						</label>
						<div class="col-sm-6">
							<label><input type="radio" name="locale" value="" /> Français</label>
							<label><input type="radio" name="locale" value="en" /> Anglais</label>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-6 col-form-label" for="mails">Emails à notifier</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" name="mails" id="mails" />
							<small>Séparés par une virgule</small>
						</div>
					</div>

					<div class="row mb-3">	
						<label class="col-sm-6 col-form-label" for="forceMembre"><input type="checkbox" id="forceMembre" value="1" /> Personnalisation du membre valideur dans Apidae
						</label>
					</div>

					<div class="alert alert-secondary showForceMembre">
						<fieldset>
							<legend>Personnalisation du membre valideur dans Apidae</legend>
								<p class="alert alert-danger">
									Champ à renseigner seulement si vous êtes certain(e) d'en avoir bien compris le fonctionnement : 
									<a href="https://aide.apidae-tourisme.com/hc/fr/articles/6791266766236-Apidae-Event-changer-le-membre-valideur" target="_blank">Voir la documentation</a>
								</p>

								<small><p>La validation des fiches dans Apidae provenant d'Apidae Event est faite, <strong>par défaut</strong> et de manière générale, par l'Office de Tourisme du territoire.
								<br />Si vous êtes dans ce cas, laissez la <strong>case vide</strong>.</p>
	
								<p>En renseignant cette information, toute manifestation saisie sur le formulaire paramétré ci-dessus sera affecté au membre choisi dans l'étape suivante.</p></small>
								
								<div class="row mb-3">
									<label class="col-sm-6 col-form-label" for="membre">Identifiant du membre valideur</label>
									<div class="col-sm-6">
										<input type="text" class="form-control" name="membre" id="membre" />
										<small><?php echo $utilisateurApidae['membre']['nom']; ?> : <?php echo $utilisateurApidae['membre']['id']; ?></small>
									</div>
								</div>
						</fieldset>
					</div>

				</form>

				<input type="button" value="Générer" class="gen" />

			</div>

		</div>

		<div class="alert alert-success">
			<h3>Configuration</h3>
			<p>Informations à envoyer à <a class="mailto" target="_blank" data-mailto="mailto:event@apidae-tourisme.zendesk.com" href="mailto:event@apidae-tourisme.zendesk.com">event@apidae-tourisme.zendesk.com</a> :</p>
			<ul id="cfg">
				<li class="territoire">Territoire : <strong></strong></li>
				<li class="membre">Membre : <strong><?php echo $utilisateurApidae['membre']['id']; ?></strong></li>
				<li class="nom_membre">Nom du membre : <strong><?php echo $utilisateurApidae['membre']['nom']; ?></strong></li>
				<li class="site_membre">Site web : <strong><?php echo $siteweb; ?></strong></li>
				<li class="mails">Emails à notifier : <strong></strong></li>
			</ul>
		</div>

		<div class="alert alert-primary">
			<h3>Code d'intégration (HTML / iframe)</h3>
			<p>Copiez-collez le code ci-dessous sur votre site web pour intégrer le formulaire</p>
			<textarea id="html" readonly="readonly"></textarea>
		</div>

		<div class="alert alert-primary" style="display:none;">
			<h3>URL de démo</h3>
			<div id="url" data-base="https://event.apidae-tourisme.<?php echo $ApidaeSso->getEnv() == 'prod' ? 'com' : $ApidaeSso->getEnv() ; ?>/"></div>
		</div>

		<div class="alert alert-primary">
			<h3>Démo</h3>
			<div id="iframe"></div>
		</div>

	</div>

	<script>
		$('.select2').select2({
			placeholder: 'Sélectionnez votre territoire',
			ajax: {
				url: './search.php',
				data: function(params) {
					return {
						q: params.term
					};
				},
				processResults: function(data) {

					console.log(data);

					if (typeof data.error !== 'undefined')
						return {
							results: [{
								'id': 0,
								'text': data.lib
							}]
						}

					var results = [];
					for (var i in data) {
						results.push({
							'id': data[i].id,
							'text': data[i].id + ' | ' + data[i].nom + ' / (' + data[i].territoireType + ' : ' + data[i].nbCommunes + ' communes)'
						});
					}

					return {
						results: results
					}
				}
			}
		});

		jQuery(document).on('change', '.form input, .form select', gen);
		jQuery(document).on('click', 'input.gen', gen);

		function gen(e) {
			var form = jQuery('.form form');

			var champs = form.find('select, input').filter(function(i) {
				return jQuery(this).val() != "" && jQuery(this).val() != 0 && jQuery(this).attr('id') != 'mails' ;
			}) ;

			var params = champs.serialize();
			console.log('params',params);

			var url = jQuery('div#url').data('base') + '?' + params;
			jQuery('div#url').html('<a href="' + url + '" target="_blank">' + url + '</a>');

			var p = document.createElement("p");

			var iframe = '<iframe src="' + url + '" frameborder="0" style="width:100%;height:3300px;"></iframe>';
			var iframe_converted = '<iframe src="' + url + '" frameborder="0" style="width:100%;height:3300px;"></iframe>';

			p.textContent = iframe;
			var iframe_converted = p.innerHTML;

			console.log('iframe',iframe) ;
			console.log('iframe_converted',iframe_converted) ;

			jQuery('#html').html(iframe_converted);
			jQuery('#iframe').html(iframe);

			jQuery('#cfg .territoire strong').html(form.find('select#territoire').val());
			jQuery('#cfg .mails strong').html(jQuery('.form').find('input#mails').val());

			var body = "";
			jQuery('#cfg li').each(function() {
				body += jQuery(this).text() + "%0D%0A\r\n";
			});

			var href = jQuery('a.mailto').data('mailto') + '?subject=Config ApidaeEvent pour ' + jQuery('#cfg .nom_membre strong').html() + '&body=' + body;
			jQuery('a.mailto').attr('href', href);

		}

		jQuery(document).on('change','#forceMembre',function(e){
			jQuery('.showForceMembre').toggle(jQuery(this).is(':checked')) ;
		}) ;
		jQuery(document).ready(function(){
			jQuery('.showForceMembre').hide() ;
		})

	</script>

</body>

</html>