# ApidaeEvent

Formulaire web permettant la suggestion d'une manifestation sur Apidae par n'importe quel internaute.

## Utilisation
### 1) iframe
Vous pouvez utiliser ce formulaire sans aucune installation, en utilisant une simple iframe :
```
<iframe src="http://apidae.allier-auvergne-tourisme.com/ApidaeEvent/?territoire=XXXXX" frameborder="0" style="width:100%;height:2000px;"></iframe>
```
Dans ce cas, renseignez dans territoire=XXXXX un identifiant de fiche "Territoire" Apidae, afin de personnaliser la liste des communes du formulaire.
Ex : pour n'afficher que les communes de la zone de compétence de l'OT de Moulins, on va utiliser la fiche [3337048](https://base.apidae-tourisme.com/consulter/objet-touristique/3337048)

>http://apidae.allier-auvergne-tourisme.com/ApidaeEvent/?territoire=3337048

Vous devez impérativement créer un projet d'écriture et en fournir les identifiants à p.granger@allier-tourisme.net en donnant notamment :
* Identifiant du membre
* Identifiant du territoire concerné (= zone de compétence du membre concerné)
* clientId (fourni par Apidae lors de la création d'un projet d'écriture)
* secret (fourni par Apidae lors de la création d'un projet d'écriture)

Sans ces informations, toutes les manifestations saisies sur le territoire seront affectées à Allier Tourisme **et seront supprimées**.

### 2) installation sur un serveur
Si vous préférez installer le formulaire sur votre site web, vous pouvez le télécharger et l'installer sur votre propre serveur (PHP/MySQL).
Vous pourrez ainsi configurer plus finement le formulaire (ajout de champs, changement de couleurs...)
Vous pouvez également contribuer au projet si vous souhaitez l'améliorer.

## Projets d'écriture
Si votre formulaire est prévu pour affecter toutes les manifestations à 1 seule membre Apidae, vous n'avez qu'un seul projet d'écriture à créer (voir `projet_ecriture_clientId` et `projet_ecriture_secret`).
En revanche si vous souhaitez que la manifestation saisie soit affectée à un autre membre, en fonction de la commune, chaque membre doit posséder son propre projet d'écriture (en attendant une API d'écriture multimembre) (voir `$_config['membres']`).

## Projet de consultation
N'importe quel projet fait l'affaire : la consultation sert juste à récupérer la liste des communes de chaque territoire. Vous pouvez donc tout à fait renseigner ici votre projet de site web par exemple.

## Configuration (pour une installation sur serveur)
* Copiez le fichier config.sample.inc.php vers config.inc.php (à la racine du dossier)
* Dans ce fichier, renseignez les informations essentielles

### config.inc.php
* **`$_config['mysqli_*']`** : Informations nécessaires à la création de 3 tables SQL. L'utilisateur configuré doit avoir les droits de création (CREATE, ALTER, INDEX)
* **`$_config['projet_consultation_apiKey']`** & **`$_config['projet_consultation_secret']`** : un projet de consultation créé sur Apidae. Vous pouvez tout à fait réutiliser les codes de votre site Internet si vous avez déjà un projet.
* **`$_config['territoire']`** (*optionnel*) : définition des communes proposées dans le formulaire : identifiant du territoire correspondant à la liste des communes proposées dans le formulaire.
	* Ex pour l'Auvergne : `$_config['territoire'] = 711392 ;` (fiche Territoire "Auvergne" sur Apidae)
* **`$_config['communes']`** (obligatoire si territoire n'est pas défini) : définition des communes proposées dans le formulaire : requête SQL permettant de filtrer sur le code INSEE en [Regexp SQL](http://www.tutorialspoint.com/mysql/mysql-regexps.htm).
	* Ex pour toutes les communes du 03 : `$_config['communes'] = '^03' ;`
	* Ex pour toutes les communes d'Auvergne : `$_config['communes'] = '^03|15|63|43' ;`
* **`$_config['projet_ecriture_clientId']`** & **`$_config['projet_ecriture_secret']`** : codes d'accès fournis par Apidae lors de la création de votre projet d'écriture
* **`$_config['projet_ecriture_multimembre']`** : actuellement (au 26/06/2017) l'API multimembre n'est pas disponible, ce paramètre doit donc rester à `false`.
* **`$_config['membres']`** (*optionnel*) : Si vous souhaitez permettre au formulaire d'enregistrer les manifestations sur différents membres (en fonction du territoire), vous devez remplir ce tableau. En revanche si votre projet est destiné à renseigner des manifestations pour 1 seul membre Apidae, les infos contenues dans projet_ecriture_* sont suffisantes.
	* Chaque membre auquel on souhaite affecter la manifestation enregistré doit avoir :
		* `id_membre` : Identifiant du membre Apidae possédant le projet d'écriture
		* `id_territoire` : Identifiant d'une fiche territoire correspondant à la zone de compétence de ce membre. A l'enregistrement, pour la commune C, faisant partie du territoire `id_territoire`, on affecte la manifestation au membre `id_membre`(grâce au projet d'écriture `clientId`+`secret`)
		* `clientId` : Code fourni lors de la création du projet d'écriture du membre id_membre
		* `secret` : Code fourni lors de la création du projet d'écriture du membre id_membre
	* Si le formulaire n'est pas en mesure de trouver le territoire concerné par la commune renseignée, la manifestation sera affectée au membre par défaut du formulaire (celui qui possède le projet projet_ecriture_clientId)
* **`$_config['types_tarifs']`** : Liste des identifiants de tarifs Apidae
* **`$_config['mail_admin']`** : Adresse mail où seront envoyées les erreurs (et les enregistrements OK si le formulaire est en `$_config['debug'] = true ;`)