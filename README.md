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

Votre animateur Apidae devra également configurer ApidaeEvent. Vous devez fournir les informations suivantes à event@apidae-tourisme.zendesk.com :
* un identifiant de territoire, ou à défaut une liste de communes (codes INSEE)
* l'identifiant de votre membre
* les adresses mails à prévenir lorsqu'une suggestion est faîte par un internaute
* l'url du site Internet sur lequel sera ajouté le formulaire

Le générateur peut également vous aider à retrouver ces informations :
>https://event.apidae-tourisme.com/gen/

Par exemple pour Moulins :
* Territoire : **3337048** ([Fiche Apidae du territoire](https://base.apidae-tourisme.com/consulter/objet-touristique/3337048))
* ID membre : **1336** ([Fiche Apidae du membre](https://base.apidae-tourisme.com/administrer/membre-sitra/1336))
* Adresse mails : contact@moulins-tourisme.fr, agenda@moulins-tourisme.fr
* Site web utilisé : https://www.moulins-tourisme.com

En effet actuellement il ne nous est pas possible de déterminer automatiquement quel membre couvre quel territoire.
> **A défaut de territoire défini, même si vous êtes abonné au projet, ApidaeEvent sera incapable de déterminer à quel membre il doit rattacher l'événement créé sur une commune donnée : la manifestation sera alors attribuée par défaut à Rhône-Alpes Tourisme (et supprimée).**



_______



## installation sur un serveur

Si vous préférez installer le formulaire sur votre site web, vous pouvez le télécharger et l'installer sur votre propre serveur (PHP).
Vous pourrez ainsi configurer plus finement le formulaire (ajout de champs, changement de couleurs...)
Vous pouvez également contribuer au projet si vous souhaitez l'améliorer.

[INSTALL.md](INSTALL.md)

## traduction

[i18n.md](i18n.md)