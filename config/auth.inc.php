<?php

    if (session_status() == PHP_SESSION_NONE) session_start() ;

    require_once(realpath(dirname(__FILE__)).'/../requires.inc.php') ;
    require_once(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require_once(realpath(dirname(__FILE__)).'/vendor/autoload.php') ;

    $ApidaeSso = new \PierreGranger\ApidaeSso($configApidaeSso,$_SESSION['ApidaeSso']) ;
    if ( isset($_GET['logout']) ) $ApidaeSso->logout() ;
    if ( isset($_GET['code']) && ! $ApidaeSso->connected() ) $ApidaeSso->getSsoToken($_GET['code']) ;

    ini_set('display_errors',0) ;
    error_reporting(E_ALL) ;

    $droits = Array('permissions'=>Array('ADMIN_DEPARTEMENTAL')) ;

    if ( $ApidaeSso->connected() )
    {
        $utilisateurApidae = $ApidaeSso->getUserProfile() ;
        $ApidaeMembres = new PierreGranger\ApidaeMembres(array_merge(
            Array('debug'=>false,'timer'=>true),
            $configApidaeMembres
        )) ;
        $usr = $ApidaeMembres->getUserById($utilisateurApidae['id']) ;
        foreach ( $droits['permissions'] as $p )
        {
            if ( ! in_array($p,$usr['permissions']) )
                die('<p>Vous ne disposez pas des permissions suffisantes ('.$p.' not in ('.implode(', ',$usr['permissions']).')</p>') ;
        }
    }

    if ( ! $ApidaeSso->connected() )
    {
        echo $ApidaeSso->form() ;
        die() ; // Assure qu'il ne se passera plus rien après, parce que l'utilisateur n'est pas identifié.
    }

    ini_set('display_errors',1) ;