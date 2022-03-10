<?php

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    //require_once(realpath(dirname(__FILE__)).'/vendor/autoload.php') ;
    require_once(realpath(dirname(__FILE__)).'/../requires.inc.php') ;

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    $expediteur_mail = 'apidaeevent@apidae-tourisme.com' ;
    $expediteur_nom = 'Apidae Event - test mail' ;

    $subject = 'Mail test php éé àà' ;
    $to      = isset($_GET['to']) ? $_GET['to'] : 'apidaeevent@apidae-tourisme.com' ;

    $message = '<h1>Test msg HTML accent : ééàà</h1>' ;
    $message .= print_r($_SERVER,true) ;

    echo '<pre>' ;

    echo 'subject='.$subject."\n" ;
    echo 'message='.$message."\n" ;
    echo 'to='.$to."\n" ;
/*
    try {
        $phpmailer = new PHPMailer\PHPMailer\PHPMailer(true) ;

        $phpmailer->setFrom($expediteur_mail,$expediteur_nom) ;

        $phpmailer->addAddress($to) ;
        $phpmailer->addBCC($expediteur_mail) ;

        $phpmailer->CharSet = 'UTF-8';

        $phpmailer->isHTML(true) ;
        $phpmailer->Subject = $subject ;
        $phpmailer->Body = $message ;

        $phpmailer->send() ;
        $sent = true ;

        echo '<hr />Test phpmailer'."\n" ;
        print_r($phpmailer) ;

    } catch (Exception $e) {
        var_dump($e) ;
        var_dump($phpmailer->ErrorInfo) ;
    }
*/
    echo 'test alerte'."\n" ;
    echo '$apidaeEvent->alerte($subject,$message,$to)=' ;
    try {
        var_dump($apidaeEvent->alerte($subject,$message,$to)) ;
    } catch (Exception $e) {
        var_dump($e) ;
    }