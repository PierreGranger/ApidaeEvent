<?php

    require_once(realpath(dirname(__FILE__)).'/../requires.inc.php') ;

    /**
     * Fonctions qui utilisent du cache
     */
    
    // getCommunesById pas utilisée ?
    
    // getCommunesByInsee Utilisée pour la génération du formulaire, non essentiel ?
    
    // getCommunesByTerritoire Utilisée pour la génération du formulaire, non essentiel ?
    
    // getOffre Utilisé seulement sur le générateur, aucun intérêt à avoir du cache

    // getTerritoires Utilisée au moment de l'enregistrement, 3 sec environ, donc à mettre en cache
    $apidaeEvent->getTerritoires(true) ;
    
    /**
     * getMembresFromCommuneInsee
     * La plus sensible et la plus longue, Appel API membres pour savoir quels sont les membres concernés par la commune saisie.
     * Le problème c'est que si on veut faire du cache ici, il faudrait cacher chaque commune afficher dans les formulaires.
     * ça permettrait de gagner 5 secondes à l'enregistrement,
     * Mais ça obligerait à mettre en cache tous les résultats pour toutes les communes, alors qu'un grand nombre n'est jamais utilisé en saisie
     */