<?php

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;
    session_start() ;

    require(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require(realpath(dirname(__FILE__)).'/../config.inc.php') ;

    use PierreGranger\ApidaeSso ;

    $configApidaeSso['debug'] = true ;
    $ApidaeSso = new ApidaeSso($configApidaeSso,$_SESSION['ApidaeSso']) ;

    if ( isset($_GET['logout']) )
    {
        $ApidaeSso->logout() ;
    }

    /* After an authentification, user is redirected to this page with a additional ?code=1234 in URL. We will use this code to get the token from SSO API */
    if ( isset($_GET['code']) && ! $ApidaeSso->connected() )
    {
        $ApidaeSso->getSsoToken($_GET['code']) ;
    }
    
    if ( $ApidaeSso->connected() ) echo '<a href="?logout">Logout</a>' ;
    else
    {
        echo '<a href="'.$ApidaeSso->getSsoUrl().'">Link to SSO Auth</a>' ;
        die() ;
    }

    // Here we know we are connected.

    echo '<pre>'.print_r($ApidaeSso->getUserProfile(),true).'</pre>' ;