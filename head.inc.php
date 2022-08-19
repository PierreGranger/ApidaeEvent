<?php


$class_label = 'col-md-2 col-sm-2';
$class_champ = 'col-md-10 col-sm-10';

if (preg_match('#Event1\.1#', $_SERVER['SCRIPT_NAME']) && preg_match('#grenoble#', $_SERVER['HTTP_REFERER'])) {
	header('location:https://apidae.allier-auvergne-tourisme.com/ApidaeEvent/?territoire=' . $_GET['territoire']);
	die();
}

$http_path = './';
if (isset($configApidaeEvent['http_path']) && $configApidaeEvent['http_path'] != '')
	$http_path = $configApidaeEvent['http_path'];

$reactEnv = ( $configApidaeEvent['debug'] ) ? 'development' : 'production.min' ;

$assets = array(
	'node_modules/jquery/dist/jquery.min.js',
	'node_modules/bootstrap/dist/js/bootstrap.min.js',
	'node_modules/bootstrap/dist/css/bootstrap.min.css',
	'node_modules/bootstrap-chosen/dist/chosen.jquery-1.4.2/chosen.jquery.min.js',
	'node_modules/bootstrap-chosen/bootstrap-chosen.css',
	'https://www.google.com/recaptcha/api.js',
	'formulaire.js?t=20210630',
	'formulaire.css?t=20210630',
	'node_modules/ajax-bootstrap-select/dist/js/ajax-bootstrap-select.min.js',
	'node_modules/ajax-bootstrap-select/dist/css/ajax-bootstrap-select.min.css',
	//'node_modules/timepicker/jquery.timepicker.min.js',
	//'node_modules/timepicker/jquery.timepicker.min.css',
	'node_modules/jquery-ui/themes/base/core.css',
	'node_modules/jquery-ui/themes/base/theme.css',
	//'node_modules/jquery-ui/themes/base/datepicker.css',
	'node_modules/jquery-ui/ui/widget.js',
	//'node_modules/jquery-ui/ui/widgets/datepicker.js',
	'node_modules/jquery-ui/ui/widgets/tooltip.js',
	//'node_modules/jquery-ui/ui/i18n/datepicker-fr.js',
	'https://unpkg.com/react@18/umd/react.'.$reactEnv.'.js',
	'https://unpkg.com/react-dom@18/umd/react-dom.'.$reactEnv.'.js',
	'https://unpkg.com/babel-standalone@6/babel.min.js',
	'mh.css',
	'mh.jsx',
	'node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css',
	'node_modules/@fortawesome/fontawesome-free/css/all.min.css',
	'node_modules/moment/min/moment.min.js',
	// 'node_modules/@fortawesome/react-fontawesome/index.js',
);

$devises = array('EUR' => '€', 'CHF' => 'CHF', 'CFP' => 'CFP');
$phone_placeholder = '00 00 00 00 00';

$theme_exclude = array();
$categorie_exclude = array();

if (isset($_GET['devise']) && isset($devises[$_GET['devise']])) {
	$devise_lib = $devises[$_GET['devise']];
	$devise_apidae = $_GET['devise'];
	/**
	 * Exceptions pour Nouvelle-Calédonie
	 */
	if ($_GET['devise'] == 'CFP') {
		$phone_placeholder = '00 00 00';
		$theme_exclude = array(
			2155, 2311, 2312, 2313, 2315, 2316, 2317, 2318, 2319, 2320, 2329, 2321, 2322, 2323, 2324, 4584, 4968 // Ski
			, 2182, 2330, 2331, 2332 // Sports de glace
			, 2259, 2341, 2342, 4104 // Sports d'hiver
		);
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
	foreach ($assets as $asset) {
		$type = substr($asset, strrpos($asset, '.') + 1);
		$local = (substr($asset, 0, 4) !== "http");
		$type = preg_replace('#\?t=[0-9]+#', '', $type);
		echo "\n\t\t";

		if ($type == 'js')
			echo '<script src="' . ($local ? './' : '') . $asset . '"></script>';
		elseif ($type == 'jsx')
			echo '<script type="text/babel" src="' . ($local ? './' : '') . $asset . '"></script>';
		else
			echo '<link rel="stylesheet" type="text/css" href="' . ($local ? './' : '') . $asset . '" media="all" />';
	}

	$icon_plus = '<span class="btn btn-primary"><i class="fas fa-plus"></i> <strong>##LIBELLE##</strong></span>';
	$icon_moins = '<span class="btn btn-warning"><i class="fas fa-minus"></i> </span>';
	?>

	<script>
		var icon_plus = '<?php echo $icon_plus; ?>';
		var icon_moins = '<?php echo $icon_moins; ?>';
		var phone_placeholder = '<?php echo $phone_placeholder; ?>';

		jQuery(document).ready(function() {
			jQuery('.chosen-select').chosen({
				disable_search_threshold: 10
			});
		});
	</script>
