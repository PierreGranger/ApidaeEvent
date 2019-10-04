# ApidaeEvent

Formulaire web permettant la suggestion d'une manifestation sur Apidae par n'importe quel internaute.

## Démo
>https://event.apidae-tourisme.com

_____
## Utilisation en iframe
Vous pouvez utiliser ce formulaire sans aucune installation, en utilisant une simple iframe.
Documentation en ligne :
>https://aide.apidae-tourisme.com/hc/fr/articles/360030771712-Apidae-Event-

Vous pouvez générer le code html correspondant à votre territoire en vous rendant sur ce générateur :
>https://event.apidae-tourisme.com/gen/

Pour que les événements saisis par l'internaute soient bien rattachés à votre fiche membre, vous devez vous abonner au projet d'API d'écriture multimembre ApidaeEvent (Diffuser > Projets > S'abonner, puis rechercher "ApidaeEvent" et cliquer sur le bouton "Abonner" à droite) :
>https://base.apidae-tourisme.com/diffuser/projet/2792

Vous devez fournir :
* un identifiant de territoire, ou à défaut une liste de communes (identifiants Apidae) et les envoyer à event@apidae-tourisme.zendesk.com :
* l'identifiant de votre membre
* les adresses mails à prévenir lorsqu'une suggestion est faîte par un internaute
* l'url du site Internet sur lequel sera ajouté le formulaire

Par exemple pour Moulins :

* ID membre : **1336** ([Fiche Apidae du membre](https://base.apidae-tourisme.com/administrer/membre-sitra/1336))
* Territoire : **3337048** ([Fiche Apidae du territoire](https://base.apidae-tourisme.com/consulter/objet-touristique/3337048))

En effet actuellement il ne nous est pas possible de déterminer automatiquement quel membre couvre quel territoire.
> **A défaut de territoire défini, même si vous êtes abonné au projet, ApidaeEvent sera incapable de déterminer à quel membre il doit rattacher l'événement créé sur une commune donnée : la manifestation sera alors attribuée par défaut à Rhône-Alpes Tourisme (et supprimée).**



_______



## installation sur un serveur

Si vous préférez installer le formulaire sur votre site web, vous pouvez le télécharger et l'installer sur votre propre serveur (PHP/MySQL).
Vous pourrez ainsi configurer plus finement le formulaire (ajout de champs, changement de couleurs...)
Vous pouvez également contribuer au projet si vous souhaitez l'améliorer.

### Configuration dans le cas d'une installation sur un serveur

> **Tout ce qui suit ne concerne que l'utilisation en "installation sur un serveur". Pour une utilisation par iframe vous n'avez pas besoin de la suite.**

#### Installation sur serveur
Clonez/téléchargez le projet sur votre serveur

```
git clone https://github.com/PGranger/ApidaeEvent.git
```

Installez les dépendances avec Composer

```
composer install
```

#### Projets d'écriture
Si votre formulaire est prévu pour affecter toutes les manifestations à 1 seule membre Apidae, vous n'avez qu'un seul projet d'écriture à créer (voir `projet_ecriture_clientId` et `projet_ecriture_secret`).
En revanche si vous souhaitez que la manifestation saisie soit affectée à un autre membre, en fonction de la commune, vous devez créer un projet d'écriture multi-membre et demander aux membres concernés de s'abonner (voir `$configApidaeEvent['membres']`).

#### Projet de consultation
N'importe quel projet fait l'affaire : la consultation sert juste à récupérer la liste des communes de chaque territoire. Vous pouvez donc tout à fait renseigner ici votre projet de site web par exemple.

#### Configuration (pour une installation sur serveur)
* Copiez le fichier config.sample.inc.php vers config.inc.php (à la racine du dossier)
* Dans ce fichier, renseignez les informations essentielles

#### config.inc.php
* **`$configApidaeEvent['mysqli_*']`** : Informations nécessaires à la création de 3 tables SQL. L'utilisateur configuré doit avoir les droits de création (CREATE, ALTER, INDEX)
* **`$configApidaeEvent['projet_consultation_apiKey']`** & **`$configApidaeEvent['projet_consultation_secret']`** : un projet de consultation créé sur Apidae. Vous pouvez tout à fait réutiliser les codes de votre site Internet si vous avez déjà un projet.
* **`$configApidaeEvent['territoire']`** (*optionnel*) : définition des communes proposées dans le formulaire : identifiant du territoire correspondant à la liste des communes proposées dans le formulaire.
	* Ex pour l'Auvergne : `$configApidaeEvent['territoire'] = 711392 ;` (fiche Territoire "Auvergne" sur Apidae)
* **`$configApidaeEvent['communes']`** (obligatoire si territoire n'est pas défini) : définition des communes proposées dans le formulaire : requête SQL permettant de filtrer sur le code INSEE en [Regexp SQL](http://www.tutorialspoint.com/mysql/mysql-regexps.htm).
	* Ex pour toutes les communes du 03 : `$configApidaeEvent['communes'] = '^03' ;`
	* Ex pour toutes les communes d'Auvergne : `$configApidaeEvent['communes'] = '^03|15|63|43' ;`
* **`$configApidaeEvent['projet_ecriture_clientId']`** & **`$configApidaeEvent['projet_ecriture_secret']`** : codes d'accès fournis par Apidae lors de la création de votre projet d'écriture
* **`$configApidaeEvent['projet_ecriture_multimembre']`** : Si votre projet est multimembre vous pouvez autoriser ce paramètre. Attention, par défaut ApidaeEvent donnera la propriété de l'offre au propriétaire du projet. Pour que les offres créées soient bien affectées au membre abonné concerné, vous devez renseigner $configApidaeEvent['membres'] en donnant id_membre et id_territoire : dans ce cas, à l'enregistrement on va chercher si la commune de l'offre enregistrée se trouve sur un id_territoire connu : si oui on l'affectera à id_membre, si non on l'affectera au propriétaire du projet.
* **`$configApidaeEvent['membres']`** (*optionnel*) : Si vous souhaitez permettre au formulaire d'enregistrer les manifestations sur différents membres (en fonction du territoire), vous devez remplir ce tableau. En revanche si votre projet est destiné à renseigner des manifestations pour 1 seul membre Apidae, les infos contenues dans projet_ecriture_* sont suffisantes.
	* Chaque membre auquel on souhaite affecter la manifestation enregistré doit avoir :
		* `id_membre` : Identifiant du membre Apidae possédant le projet d'écriture
		* `id_territoire` : Identifiant d'une fiche territoire correspondant à la zone de compétence de ce membre. A l'enregistrement, pour la commune C, faisant partie du territoire `id_territoire`, on affecte la manifestation au membre `id_membre`(grâce au projet d'écriture `clientId`+`secret`)
	* Si le formulaire n'est pas en mesure de trouver le territoire concerné par la commune renseignée, la manifestation sera affectée au membre par défaut du formulaire (celui qui possède le projet projet_ecriture_clientId)
* **`$configApidaeEvent['types_tarifs']`** : Liste des identifiants de tarifs Apidae
* **`$configApidaeEvent['mail_admin']`** : Adresse mail où seront envoyées les erreurs (et les enregistrements OK si le formulaire est en `$configApidaeEvent['debug'] = true ;`)
* **`$configApidaeEvent['recaptcha_secret']`** & **`$configApidaeEvent['recaptcha_sitekey']`** (optionnel) : Pour afficher un bouton "Je ne suis pas un robot" : https://www.google.com/recaptcha/
