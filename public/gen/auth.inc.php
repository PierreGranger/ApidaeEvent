<?php

use PierreGranger\ApidaeSso;

if (session_status() == PHP_SESSION_NONE) session_start();

require_once(realpath(dirname(__FILE__)).'/../../src/requires.inc.php') ;
require_once(realpath(dirname(__FILE__)).'/../../vendor/autoload.php') ;

$ApidaeSso = new ApidaeSso($configApidaeSso,$_SESSION['ApidaeSso']) ;
if ( isset($_GET['logout']) ) $ApidaeSso->logout() ;
if ( isset($_GET['code']) && ! $ApidaeSso->connected() ) $ApidaeSso->getSsoToken($_GET['code']) ;

if ( ! $ApidaeSso->connected() )
{
    echo $ApidaeSso->form() ;
    die() ; // Assure qu'il ne se passera plus rien après, parce que l'utilisateur n'est pas identifié.
}
