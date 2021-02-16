<?php

    if (session_status() == PHP_SESSION_NONE) session_start() ;

    require_once(realpath(dirname(__FILE__)).'/../requires.inc.php') ;
    require_once(realpath(dirname(__FILE__)).'/../vendor/autoload.php') ;
    require_once(realpath(dirname(__FILE__)).'/vendor/autoload.php') ;

    $ApidaeSso = new \PierreGranger\ApidaeSso($configApidaeSso,$_SESSION['ApidaeSso']) ;
    if ( isset($_GET['logout']) ) $ApidaeSso->logout() ;
    if ( isset($_GET['code']) && ! $ApidaeSso->connected() ) $ApidaeSso->getSsoToken($_GET['code']) ;
    
    ini_set('display_errors',1) ;
    error_reporting(E_ALL) ;

    $droits = Array('permissions'=>Array('ADMIN_DEPARTEMENTAL')) ;
    
    if ( $ApidaeSso->connected() )
    {
        $utilisateurApidae = $ApidaeSso->getUserProfile() ;
        $ApidaeMembres = new PierreGranger\ApidaeMembres($configApidaeMembres) ;
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

    $types = Array('offices','departements') ;

    if ( isset($_POST['json']) && isset($_GET['type']) && in_array($_GET['type'],$types) )
    {
        $retour = Array() ;

        $retour['erreur'] = 0 ;

        $json_edite = json_encode($_POST['json']) ;
        if ( json_last_error() != JSON_ERROR_NONE )
        {
            $retour['erreur'] = 'Le formulaire n\'est pas conforme' ;
        }

        $ok = file_put_contents(realpath(dirname(__FILE__)).'/'.$_GET['type'].'.json',$json_edite) ;
        if ( $ok === false )
        {
            $retour['erreur'] = 'Ecriture du fichier de config impossible' ;
        }

        if ( $retour['erreur'] == 0 )
        {
            $json_edite = json_encode($_POST['json'],JSON_PRETTY_PRINT) ;
            
            $to = $configApidaeEvent['mail_admin'][0] ;
            $subject = 'ApidaeEvent : config '. $utilisateurApidae['firstName'].' '.$utilisateurApidae['lastName'] . ' #' . $utilisateurApidae['id'] ;

            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
            $headers[] = 'From: Apidae Event <'.$configApidaeEvent['mail_expediteur'].'>';
            $headers[] = 'To: '.$to ;
            
            $message = '<pre>'.$json_edite.'</pre>' ;

            // Envoi
            mail($to, $subject, $message, implode("\r\n", $headers));

            $retour['json'] = $_POST['json'] ;
        }

        echo json_encode($retour) ;

        return ;
    }

?><!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
        <script
            src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
            integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
            crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="./jsoneditor.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

		<?php if ( file_exists(realpath(dirname(__FILE__)).'/../analytics.php') )
			include(realpath(dirname(__FILE__)).'/../analytics.php') ; ?>
        <script>
            var k = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65],
            n = 0;
            $(document).keydown(function (e) {
                if (e.keyCode === k[n++]) {
                    if (n === k.length) {
                        jQuery('code').show() ;
                        n = 0;
                        return false;
                    }
                }
                else {
                    n = 0;
                }
            });
        </script>
        <script>
            var schema = {
                "type": "array",
                "format": "table",
                "items": {
                        "type" : "object",
                        "properties" : {
                            "nom": {"type" : "string"},
                            "site": {"type" : "string"},
                            "id_membre": { "type": "number" },
                            "id_territoire": { "type": "number" },
                            "parrain": {"type" : "string"},
                            "mail" : {
                                "type" : "array",
                                "format" : "table",
                                "items" : {
                                    "type" : "string",
                                    "format" : "email"
                                }
                            },
                            "insee_communes" : {
                                "type" : "array",
                                "format" : "table",
                                "items" : { "type" : "number" }
                            }
                        }
                    }
            }

        </script>
        <script>
            jQuery(document).on('click','button#reset',function(){
                var resetval = JSON.parse(jQuery('#startval').val()) ;
                console.log('reset') ;
                console.log(resetval) ;
                editor.setValue(resetval) ;
            }) ;
            jQuery(document).on('click','#enregistrer',function(){

                var errors = editor.validate() ;
                if ( errors.length )
                {
                    alert('Votre formulaire comporte des erreurs (cf console : F12)') ;
                    console.log(errors) ;
                    return false ;
                }

                var ajax = jQuery.ajax({
                    method:'post',
                    dataType:'json',
                    url:'./?type='+jQuery('input[name="type"]').val(),
                    data:{'json':editor.getValue()}
                }) ;
                ajax.fail(function(e){
                    alert(e) ;
                    console.log(e) ;
                }) ;
                ajax.done(function(e){
                    if ( e['erreur'] != '0' )
                    {
                        console.log(e) ;
                        alert(e['erreur']) ;
                        return false ;
                    }
                    alert('Enregistrement effectué') ;
                    if ( typeof e['json'] !== 'undefined' ) editor.setValue(e['json']) ;
                }) ;
            }) ;
        </script>
        <style>
            input[type="email"] {
                font-family:monospace ;
                font-size:10px ;
                width:300px ;
            }
        </style>
	</head>
	<body>

        <?php

            if ( ! isset($_GET['type']) )
            {
                echo '<div style="margin:0 auto;width:300px;margin-top:200px;">' ;
                    echo '<h1>Config</h1>' ;
                    echo '<p>Pour simplifier la configuration (éviter d\'avoir à remonter les offices au dessus des départements), la config est désormais découpée en 2.</p>' ;
                    foreach ( $types as $t )
                        echo '<a class="btn btn-primary" href="?type='.$t.'">'.ucfirst($t).'</a> &nbsp; ' ;
                echo '</div>' ;
            }
            if ( isset($_GET['type']) && in_array($_GET['type'],$types) )
            {

                $startval = file_get_contents(realpath(dirname(__FILE__)).'/'.$_GET['type'].'.json') ;

                /*
                if ( isset($_GET['reset']) )
                {
                    include(realpath(dirname(__FILE__)).'/../config.inc.php') ;
                    $membres = $configApidaeEvent['membres'] ;
                    foreach ( $membres as $k => $mb )
                    {
                        if ( isset($mb['secret']) ) { unset($membres[$k]) ; continue ; }
                        if ( ! is_array($mb['mail']) ) $membres[$k]['mail'] = Array($mb['mail']) ;
                    }
                    $startval = json_encode($membres) ;
                }
                */
                
                ?>

                    <input type="hidden" name="type" value="<?php echo $_GET['type'] ; ?>" />
                    <div id="editor_holder"></div>
                    <button id="enregistrer">Enregistrer</button>

                    <code style="display:none;">
                        <hr />
                        <textarea id="startval" style="width:100%; height:500px;"><?php echo $startval ; ?></textarea>
                    </code>

                    <script>
                        var element = document.getElementById('editor_holder');
                        var startval = JSON.parse(document.getElementById('startval').innerText) ;
                        console.log(startval) ;
                        var editor = new JSONEditor(element,{
                            theme: 'jqueryui',
                            disable_collapse : true,
                            disable_edit_json : true,
                            disable_properties : true,
                            schema: schema,
                            startval : startval
                        });
                    </script>

                <?php
            }
        ?>

	</body>

</html>