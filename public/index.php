<?php
	require_once(realpath(dirname(__FILE__)) . '/../src/requires.inc.php');

	$ko = [] ;
	$ok = [] ;
	$display_form = true;

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
				include(realpath(dirname(__FILE__)) . '/../src/index.post.php');
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