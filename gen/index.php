<?php

if (session_status() == PHP_SESSION_NONE) session_start();

require_once(realpath(dirname(__FILE__)) . '/../requires.inc.php');
require_once(realpath(dirname(__FILE__)) . '/../vendor/autoload.php');
require_once(realpath(dirname(__FILE__)) . '/vendor/autoload.php');

$ApidaeSso = new \PierreGranger\ApidaeSso($configApidaeSso, $_SESSION['ApidaeSso']);
if (isset($_GET['logout'])) $ApidaeSso->logout();
if (isset($_GET['code']) && !$ApidaeSso->connected()) $ApidaeSso->getSsoToken($_GET['code']);

$refresh = isset($_GET['refresh']) ;

if (!$ApidaeSso->connected()) {
	$ssoUrl = $ApidaeSso->getSsoUrl();

?><html>

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<script>
			(function(i, s, o, g, r, a, m) {
				i['GoogleAnalyticsObject'] = r;
				i[r] = i[r] || function() {
					(i[r].q = i[r].q || []).push(arguments)
				}, i[r].l = 1 * new Date();
				a = s.createElement(o),
					m = s.getElementsByTagName(o)[0];
				a.async = 1;
				a.src = g;
				m.parentNode.insertBefore(a, m)
			})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
			<?php
			$uid = isset($utilisateurApidae) ? ',{userId:' . $utilisateurApidae['id'] . '}' : '';
			?>
			ga('create', 'UA-101563357-1', 'auto'
				<?php echo $uid; ?>);
			ga('send', 'pageview');
		</script>
		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	</head>

	<body>

		<div class="container theme-showcase">
			<div class="jumbotron">
				<h1>Espace réservé</h1>
				<p>Cet espace est réservé : vous pouvez y accéder en vous authentifiant sur Apidae.</p>
				<p><a class="btn btn-primary btn-lg" role="button" href="<?php echo $ssoUrl; ?>">S'authentifier sur Apidae</a></p>
			</div>
		</div>
	</body>

	</html><?php die(); // Assure qu'il ne se passera plus rien après, parce que l'utilisateur n'est pas identifié.
		}

		$utilisateurApidae = $ApidaeSso->getUserProfile();

		if (!isset($ApidaeEvent)) die('no $ApidaeEvent');
		$siteweb = null;
		$ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres);
		$membre = $ApidaeMembres->getMembreById($utilisateurApidae['membre']['id']);
		if (isset($membre['entitesJuridiques'][0]['id'])) {
			$entite = $ApidaeEvent->getOffre($membre['entitesJuridiques'][0]['id'],null,$refresh);
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
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>

	<?php if (file_exists(realpath(dirname(__FILE__)) . '/../../analytics.php'))
		include(realpath(dirname(__FILE__)) . '/../../analytics.php'); ?>

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

		<h1>ApidaeEvent - aide à la configuration</h1>

		<div class="jumbotron">

			<p>Aide en ligne : <a href="https://aide.apidae-tourisme.com/hc/fr/articles/360030771712-Apidae-Event-">https://aide.apidae-tourisme.com/hc/fr/articles/360030771712-Apidae-Event-</a></p>
			<!--<p>Documentation : <a target="_blank" href="https://github.com/PGranger/ApidaeEvent">https://github.com/PGranger/ApidaeEvent</a></p>-->

			<div class="form">

				<form method="get">

					<div class="form-group row">
						<label class="col-4 col-form-label" for="territoire">Territoire</label>
						<div class="col-8">
							<select id="territoire" name="territoire" class="select2 js-example-placeholder-single js-states form-control"></select>
							<br />
							<small>Le choix du territoire conditionne la liste des communes proposées dans le formulaire</small>
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="contactObligatoire">Contact obligatoire
							<br /><small>1 Mail ou 1 téléphone minimum dans la zone "Contact"</small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="contactObligatoire" name="contactObligatoire" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="TourismeAdapte">Tourisme Adapté
							<br /><small>Champs principaux de "Prestations > Accueil des personnes en situation de handicap > Tourisme adapté"</small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="TourismeAdapte" name="TourismeAdapte" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="clientele">Types de clientèle
							<br /><small>Ajout des champs Types de clientèles (Offres adaptées à des clientèles spécifiques + Clientèle pratiquant une activité spécifique)</small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="clientele" name="clientele" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="generique">Evenements génériques & championnats
							<br /><small>Journées du patrimoine...</small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="generique" name="generique" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="reservation">Réservation
							<br /><small></small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="reservation" name="reservation" value="1" />
						</div>
					</div>


					<div class="form-group row">
						<label class="col-4 col-form-label" for="mm">Multimédias
							<br /><small>Permettre l'ajout de multimédias (PDF seulement)</small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="mm" name="mm" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="copyright">Copyright obligatoire
							<br /><small>Si une illustration est ajoutée elle doit avoir un copyright renseigné</small>
						</label>
						<div class="col-8">
							<input type="checkbox" id="copyright" name="copyright" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="illustrationMini">Taille mini illustration
							<br /><small>Si une illustration est ajoutée, elle doit obligatoirement faire + de X pixels (ex: 1200)</small>
							<br /><small>Laisser à 0 pour ne pas demander de taille minimale</small>
						</label>
						<div class="col-8">
							<input type="number" id="illustrationMini" name="illustrationMini" value="" min="0" max="2000" step="200" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="illustrationObligatoire">1 illustration obligatoire minimum
						</label>
						<div class="col-8">
							<input type="checkbox" id="illustrationObligatoire" name="illustrationObligatoire" value="1" />
						</div>
					</div>

					<div class="form-group row">
						<label class="col-4 col-form-label" for="devise">Devise</label>
						<div class="col-8">
							<label><input type="radio" id="devise_eur" name="devise" value="" /> €</label>
							<label><input type="radio" id="devise_chf" name="devise" value="CHF" /> Franc suisse (CHF)</label>
							<label><input type="radio" id="devise_cfp" name="devise" value="CFP" /> Franc pacifique (CFP)</label>

						</div>
					</div>

				</form>

				<div class="form-group row">
					<label class="col-4 col-form-label" for="mails">Emails à notifier</label>
					<div class="col-8">
						<input type="text" class="form-control" name="mails" id="mails" />
						<small>Séparés par une virgule</small>
					</div>
				</div>

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
			<div id="url" data-base="https://event.apidae-tourisme.com/"></div>
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
				return jQuery(this).val() != "" && jQuery(this).val() != 0 ;
			}) ;

			var params = champs.serialize();
			console.log(params);

			var url = jQuery('div#url').data('base') + '?' + params;
			jQuery('div#url').html('<a href="' + url + '" target="_blank">' + url + '</a>');

			var iframe = '<iframe src="' + url + '" frameborder="0" style="width:100%;height:3300px;"></iframe>';
			jQuery('#html').html(iframe);
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
	</script>

</body>

</html>