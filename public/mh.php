<?php

require_once(realpath(dirname(__FILE__)) . '/../src/requires.inc.php');

?><!DOCTYPE html>
<html lang="fr">

<head>
	<?php include(realpath(dirname(__FILE__)) . '/../src/head.inc.php') ; ?>
</head>
<body>

		<div class="container">
		<form class="form" method="post" enctype="multipart/form-data" novalidate>
			<fieldset>
				<legend>Dates et horaires</legend>
				<div id="multihoraire"></div>
			</fieldset>
		</form>
		</div>

</body>
</html>