# ApidaeEvent

Formulaire web permettant la suggestion d'une manifestation sur Apidae par n'imp
orte quel internaute.

## Configuration
* Copiez le fichier config.sample.inc.php vers config.inc.php (à la racine du dossier)
* Dans ce fichier, renseignez les informations essentielles

### config.inc.php
* **`$_config['mysqli_*']`** : Informations nécessaires à la création de 3 tables SQL. L'utilisateur configuré doit avoir les droits de création (CREATE, ALTER, INDEX)
* **`$_config['projet_consultation_apiKey']`** & **`$_config['projet_consultation_secret']`** : un projet de consultation créé sur Apidae. Vous pouvez tout à fait réutiliser les codes de votre site Internet si vous avez déjà un projet.
* **`$_config['territoire']`** (optionnel) : définition des communes proposées dans le formulaire : identifiant du territoire correspondant à la liste des communes proposées dans le formulaire.
	* Ex pour l'Auvergne : `$_config['territoire'] = 711392 ;` (fiche Territoire "Auvergne" sur Apidae)
* **`$_config['communes']`** (obligatoire si territoire n'est pas défini) : définition des communes proposées dans le formulaire : requête SQL permettant de filtrer sur le code INSEE en [Regexp SQL](http://www.tutorialspoint.com/mysql/mysql-regexps.htm).
	* Ex pour toutes les communes du 03 : `$_config['communes'] = '^03' ;`
	* Ex pour toutes les communes d'Auvergne : `$_config['communes'] = '^03|15|63|43' ;`
* **`$_config['projet_ecriture_clientId']`** & **`$_config['projet_ecriture_secret']`** : codes d'accès fournis par Apidae lors de la création de votre projet d'écriture
* **`$_config['projet_ecriture_multimembre']`** : actuellement (au 26/06/2017) l'API multimembre n'est pas disponible, ce paramètre doit donc rester à `false`.
* **`$_config['membres']`** : 
* **`$_config['types_tarifs']`** : 
* **`$_config['mail_admin']`** : 
		'membres' => Array(
			Array('id_membre'=>1366,		'id_territoire'=>4593560,	'clientId'=>null,	'secret'=>null), // Commentry - Néris
			Array('id_membre'=>1538,		'id_territoire'=>4719368,	'clientId'=>null,	'secret'=>null), // Entr'Allier Besbre et Loire
			Array('id_membre'=>1336,		'id_territoire'=>3337048,	'clientId'=>null,	'secret'=>null), // Moulins
			Array('id_membre'=>1541,		'id_territoire' =>null,		'clientId'=>null,	'secret'=>null), // Vichy
			Array('id_membre'=>1147,		'id_territoire'=>742838,	'clientId'=>null,	'secret'=>null), // Allier
			Array('id_membre'=>1182,		'id_territoire'=>742848,	'clientId'=>null,	'secret'=>null), // Cantal
			Array('id_membre'=>1158,		'id_territoire'=>837264,	'clientId'=>null,	'secret'=>null), // Haute Loire
			Array('id_membre'=>1180,		'id_territoire'=>742842,	'clientId'=>null,	'secret'=>null), // Puy de Dôme
			Array('id_membre'=>1,			'id_territoire'=>711392,	'clientId'=>null,	'secret'=>null), // Auvergne Rhône Alpes
		),
		'types_tarifs' => Array(5239,5240,1754,4204,1755,4123,4134,4102,4099,4100,4101),
		'mail_admin' => 'p.granger@allier-tourisme.net'