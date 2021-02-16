<?php

    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    $membres = json_decode(file_get_contents(realpath(dirname(__FILE__)).'/membres.json'),true) ;

    include(realpath(dirname(__FILE__)).'/../../ApidaeMembres/vendor/autoload.php') ;
    include(realpath(dirname(__FILE__)).'/../../ApidaeMembres/config.inc.php') ;

    echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
     ' ;

    $ApidaeMembres = new \PierreGranger\ApidaeMembres($configApidaeMembres) ;

    foreach ( $membres as $m )
    {
        //if ( @$i++ > 44 ) break ;

        //if ( ! in_array($m['id_membre'],Array(29,263)) ) continue ;

        $mails_config = $m['mail'] ;
        $m['mails'] = Array() ;
        unset($m['mail']) ;
        sort($mails_config) ;

        $infos_api = Array() ;
        $mails_api = Array() ;

        try {
            $infos = $ApidaeMembres->getMembreById($m['id_membre']) ;
            $infos_api['id'] = $infos['id'] ;
            $infos_api['nom'] = $infos['nom'] ;
            $infos_api['mails'] = Array() ;

            $utilisateurs = $ApidaeMembres->getUsersByMember($m['id_membre'],Array('UTILISATEURS')) ;
    
            foreach ( $utilisateurs as $u )
            {
                if ( isset($u['permissions']) && is_array($u['permissions']) && in_array('VALIDEUR_API_ECRITURE',$u['permissions']) )
                    $mails_api[] = $u['contact']['eMail'] ;
            }
            $infos_pre = Array(
                'id' => $infos['id'],
                'nom' => $infos['nom']
            ) ;
        } catch ( Exception $e )
        {
            //print_r($e) ;
        }

        sort($mails_api) ;

        foreach ( $mails_config as $k => $v )
            $m['mails'][] = '<span class="alert-'.( ! in_array($v,$mails_api) ? 'danger':'success' ) .'">'.$v.'</span>' ;

        foreach ( $mails_api as $k => $v )
            $infos_api['mails'][] = '<span class="alert-'.( ! in_array($v,$mails_config) ? 'danger':'success' ) .'">'.$v.'</span>' ;

        echo "\n".'<hr style="clear:both;" />' ;
        echo '<h1>'.$m['id_membre'].' / '.$m['nom'].'</h1>' ;
        echo '<div style="width:45%;float:left;">' ;
            echo '<h2>Config manuelle</h2>' ;
            echo '<pre>' ;
                print_r($m) ;
            echo '</pre>' ;
        echo '</div>' ;
        echo '<div style="width:45%;float:right;">' ;
            echo '<h2>Infos API Membres</h2>' ;
            echo '<pre>' ;
                print_r($infos_api) ;
            echo '</pre>' ;
        echo '</div>' ;
    }
