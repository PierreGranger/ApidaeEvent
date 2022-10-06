<?php

$http_path = './';
if (isset($configApidaeEvent['http_path']) && $configApidaeEvent['http_path'] != '')
	$http_path = $configApidaeEvent['http_path'];

$devises = ['EUR' => '€', 'CHF' => 'CHF', 'CFP' => 'CFP'];
$phone_placeholder = '00 00 00 00 00';

$theme_exclude = [] ;
$categorie_exclude = [] ;

if (isset($_GET['devise']) && isset($devises[$_GET['devise']])) {
	$devise_lib = $devises[$_GET['devise']];
	$devise_apidae = $_GET['devise'];
	/**
	 * Exceptions pour Nouvelle-Calédonie
	 */
	if ($_GET['devise'] == 'CFP') {
		$phone_placeholder = '00 00 00';
		$theme_exclude = [
			2155, 2311, 2312, 2313, 2315, 2316, 2317, 2318, 2319, 2320, 2329, 2321, 2322, 2323, 2324, 4584, 4968 // Ski
			, 2182, 2330, 2331, 2332 // Sports de glace
			, 2259, 2341, 2342, 4104 // Sports d'hiver
		];
	} elseif ($_GET['devise'] == 'CHF') {
		$phone_placeholder = '+41 00 000 00 00';
	}
} else {
	$devise_lib = '€';
	$devise_apidae = 'EUR';
}

$multiHoraire = isset($_GET['mh']) && $_GET['mh'] == 1 ;

?><meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	$icon_plus = '<span class="btn btn-primary"><i class="fas fa-plus"></i> <strong>##LIBELLE##</strong></span>';
	$icon_moins = '<span class="btn btn-warning"><i class="fas fa-minus"></i> </span>';
	?>

<?php
	$manifest = json_decode(file_get_contents(realpath(dirname(__FILE__)).'/../public/build/manifest.json'),true) ;
	if ( json_last_error() !== JSON_ERROR_NONE ) die('Assets error') ;
?>

<script src="<?php echo $manifest['build/app.js'] ; ?>"></script>
<script defer src="<?php echo $manifest['build/runtime.js'] ; ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $manifest['build/app.css'] ; ?>" media="all" />

<script>
	var icon_plus = '<?php echo $icon_plus; ?>';
	var icon_moins = '<?php echo $icon_moins; ?>';
	var phone_placeholder = '<?php echo $phone_placeholder; ?>';
</script>

<script src="https://www.google.com/recaptcha/api.js"></script>

<?php if ( isset($_GET['apihours']) ) { ?>
	<link href="https://form.apihours.apidae-tourisme.<?php echo isset($config['apihours']['env']) ? $config['apihours']['env'] : 'com' ; ?>/0.6.0/styles.css" rel="stylesheet"/>
<?php } ?>