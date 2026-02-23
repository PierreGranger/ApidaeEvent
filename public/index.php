<?php

	ini_set('display_errors',1) ;
	error_reporting(E_ERROR) ;

	require_once(realpath(dirname(__FILE__)) . '/../src/requires.inc.php');

	// Variable $show : priorité au paramètre URL show=JSON, sinon construite à partir des anciens paramètres GET
	$show = [];
	if (isset($_GET['show'])) {
		$decoded = json_decode($_GET['show'], true);
		if (is_array($decoded)) {
			$show = $decoded;
		}
	} else { // rétrocompatibilité : pour ceux qui ont généré leur URL avant l'usage de $show, on ajoute les anciens champs classiques
		$show[] = 'a2' ;
		$show[] = 'a3' ;
		$show[] = 'type' ;
		$show[] = 'cat' ;
		$show[] = 'photos' ;
	}
	if (empty($show)) {
		if (!empty($_GET['toutou'])) $show[] = 'animaux';
		if (!empty($_GET['generique'])) $show[] = 'gen';
		if (!empty($_GET['mm'])) $show[] = 'mm';
		if (!empty($_GET['TourismeAdapte'])) $show[] = 'ta';
		if (!empty($_GET['clientele'])) $show[] = 'cli';
		if (!empty($_GET['reservation'])) $show[] = 'resa';
	}

	$ko = [] ;
	$ok = [] ;
	$display_form = true;

	if ( isset($_GET['testMemCached']) ) {
		var_dump($apidaeEvent->testMemCached()) ;
		return false ;
	}

?><!DOCTYPE html>
<html lang="fr">

<head><base href="/">

	<?php include(realpath(dirname(__FILE__)) . '/../src/head.inc.php') ; ?>
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
			</script>
		<?php } ?>
		
		<?php

			if (isset($_POST['nom'])) {
				include(realpath(dirname(__FILE__)) . '/../src/post.inc.php');
			}

			$post = $_POST;
			if (!is_array($post)) $post = [];

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
			<?php } ?>

		<?php
			if ($configApidaeEvent['debug'] && isset($_GET['showAbonnes'])) {
				include(realpath(dirname(__FILE__)) . '/../src/showAbonnes.inc.php');
			}
		?>

		<?php
			if ($display_form) {
				include(realpath(dirname(__FILE__)).'/../src/form.inc.php') ;
			}
		?>

	</div>

</body>

</html>