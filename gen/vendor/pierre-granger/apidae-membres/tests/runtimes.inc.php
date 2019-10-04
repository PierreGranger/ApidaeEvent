<?php

if ( ! isset($rutime) ) $rutime = Array() ;

if ( ! function_exists('ruStart') )
{
    function ruStart($nom='Time') {
        global $rutime ;
        $rutime[$nom] = microtime('true') ;
    }
}

if ( ! function_exists('ruShow') )
{
    function ruShow($nom='Time') {
        global $rutime ;
        $execution_time = round( microtime('true') - $rutime[$nom],2 ) ;
        $echo = $nom.' : '.$execution_time.'s' ;
        echo '<pre style="font-size:0.8em;">'.$echo.'</pre>' ;
    }
}