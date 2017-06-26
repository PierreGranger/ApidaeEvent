<?php

	/**
	*
	*	La base SQL sert à stocker la liste des communes Apidae et les éléments de référence (portée, thèmes, catégories...)
	*	
	*	@param projet_consultation_projetId int 	Numéro de votre projet de consultation sur Apidae. Il peut s'agir d'un projet de consultation existant (celui de votre site web par exemple) : la seule chose importante est qu'il y existe une sélection de territoires.
	*	@param projet_consultation_apiKey	string 	Clé du projet de consultation.
	*	@param selection_territoires 	int 	Identifiant de la sélection qui contient toutes les fiches territoires des offices concernés
	*	@param membres Array Tableau permettant le lien entre le membre Apidae concerné et la fiche territoire. Pour le membre M, il faut qu'il existe sur Apidae une fiche territoire T dont la liste des communes correspond à la zone de compétence du membre M. A l'enregistrement, on va rechercher sur quel territoire se trouve la commune saisie par l'utilisateur : on en déduira le membre concerné (ex: commune saisie 1425 (Moulins) => Territoire 3337048 (Zone compétence Moulins) => id_membre 1336 (Office du Tourisme de Moulins) ). Attention : si plusieurs territoires se chevauchent (ex: OT et CDT), saisissez dans l'ordre de "saisie" (par ex. on saisira d'abord les membres "offices, puis le membre "département" : si la commune est sur le territoire d'un office on lui donne, sinon on le donne au département)
	Attention : pour chaque membre on PEUT présicer clientId et secret : il s'agit des projets d'écritures concernant ces membres. Si ces valeurs sont renseignées, le projet d'écriture utilisé sera celui-ci et non le projet par défaut (projet_ecriture_clientId ou projet_ecriture_secret) : le but est de palier à l'absence d'API d'écriture multimembre (qui devrait sortir seconde moitié 2017).
	*	@param territoire int Identifiant du territoire proposé pour la saisie. Si ce paramètre est saisi, le paramètre communes sera inutilisé.
	*	@param communes string  REGEXP SQL sur le champ code (insee) http://www.tutorialspoint.com/mysql/mysql-regexps.htm pour déterminer la liste des communes à proposer dans le formulaire de saisie.
	*	@param projet_ecriture_multimembre bool Le projet d'écriture permet-il d'écrire sur d'autres membres que celui à qui appartient le projet ? Autrement dit, peut-on changer gestion.membreProprietaire.type.id ? Au 21/06/2017, l'API d'écriture multi membre n'existe pas et ce paramètre ne peut donc être qu'à false. Il faut donc préciser pour chaque membre dans la config un clientId et un secret selon chaque projet d'écriture pour que l'offre atterisse chez eux.
	*	@param mail_admin $string Adresse mail où seront envoyées les erreurs (et les enregistrements OK si le formulaire est en debug 1)
	*
	**/

	$_config = Array(
		'debug' => true,
		'mysqli_user' => 'apidae',
		'mysqli_db' => 'apidae',
		'mysqli_password' => 'apidae',
		'projet_consultation_apiKey' => 'xxxxx',
		'projet_consultation_projetId' => 0000,
		'projet_ecriture_clientId' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx',
		'projet_ecriture_secret' => 'xxxxx',
		'projet_ecriture_multimembre' => false,
		'communes' => '^03|15|63|43',
		'territoire' => 711392, // Auvergne
		'selection_territoires' => 53994,
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
	) ;