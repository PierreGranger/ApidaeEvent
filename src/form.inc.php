<?php

    $class_line = 'field row' ;
    $class_label = '';
    $class_champ = ''; 

?>

<form class="form" method="post" enctype="multipart/form-data" novalidate>

    <?php $referer = (isset($_POST['referer'])) ? $_POST['referer'] : @$_SERVER['HTTP_REFERER']; ?>
    <input type="hidden" name="referer" value="<?php echo htmlentities($referer); ?>">
    <input type="hidden" name="devise" value="<?php echo htmlentities($devise_apidae); ?>">

    <fieldset class="form-group required">
        
        <div class="cardHeader">
            <legend><?php __('Informations générales') ; ?></legend>
            <p><?php __('Décrivez votre événement en quelques mots') ; ?></p>
        </div>
        
        <div class="<?= $class_line ; ?> required">
            <label for="portee" class="<?= $class_label; ?> col-form-label"> <?php __('Nom de la manifestation') ; ?></label>
            <div class="controls">
                <input class="form-control" name="nom" type="text" value="<?php echo htmlentities(@$post['nom']); ?>" placeholder="<?php echo htmlentities(_('Entrez le nom de votre événement')); ?>" id="nom" required="required">
            </div>
        </div>

        <div class="<?= $class_line ; ?> required">
            <label for="portee" class="<?= $class_label; ?> col-form-label"> <?php __('Importance de votre événement') ; ?> <span class="sub"><?php __('Portée') ; ?></span> <i class="fas fa-info-circle" title="<?php __('La portée concerne les spectateurs et la distance qu’ils sont prêt à parcourir pour participer à une manifestation.') ; ?>"></i></label>
            <div class="<?= $class_champ ; ?>">
                <select class="form-select" name="portee" id="portee" required="required" data-placeholder="<?php echo htmlentities(_('Sélectionnez la portée')) ; ?>">
                    <option value="" disabled="disabled" <?php if (! isset($post['portee']) ) echo ' selected="selected"'; ?>><?php echo htmlentities(_('Sélectionnez la portée')) ; ?></option>
                    <?php

                    $FeteEtManifestationPortees = $apidaeEvent->getElementsReferenceByType('FeteEtManifestationPortee');
                    foreach ($FeteEtManifestationPortees as $option) {
                        echo '<option value="' . $option['id'] . '"';
                        if (isset($option['description'])) echo ' title="' . htmlspecialchars($option['description']) . '" ';
                        if (isset($post['portee']) && $post['portee'] == $option['id']) echo ' selected="selected"';
                        echo '>' . $apidaeEvent->libelleEr($option) . '</option>';
                    }

                    ?>
                </select>
            </div>
        </div>

        <?php if ( in_array('part', $show) ) { ?>
        <div class="row">
            <div class="field col-sm-6">
                <label for="nbParticipantsAttendu" class="col-form-label"><?php __('Participants attendus') ;?></label>
                <input class="form-control" type="number" name="nbParticipantsAttendu" id="nbParticipantsAttendu" value="<?php echo htmlentities(@$post['nbParticipantsAttendu']); ?>">
            </div>
            <div class="field col-sm-6">
                <label for="nbVisiteursAttendu" class="col-form-label"><?php echo __('Visiteurs attendus') ; ?></label>
                <input class="form-control" type="number" name="nbVisiteursAttendu" id="nbVisiteursAttendu" value="<?php echo htmlentities(@$post['nbVisiteursAttendu']); ?>">
            </div>
        </div>
        <?php } ?>
        
    </fieldset>

    <fieldset>

        <div class="cardHeader">
            <legend><?php __('Adresse') ; ?></legend>
            <p><?php __('Où se déroule votre événement ?') ; ?></p>
        </div>

        <div class="<?= $class_line ;?>">
            <label for="adresse1" class="<?php echo $class_label; ?> col-form-label"><?php __('Adresse 1') ; ?>
                <i class="fas fa-info-circle" title="<?php __('Voie et bâtiment. Exemple : 60 rue des Lilas - Bâtiment A. Pas de virgule mais un espace entre le numéro et le nom de la rue.') ; ?>"></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="adresse1" value="<?php echo htmlentities(@$post['adresse1']); ?>" placeholder="<?php echo htmlentities(_('Numéro et nom de rue')) ; ?>">
            </div>
        </div>
        <?php if ( in_array('a2', $show) ) { ?>
        <div class="<?= $class_line ;?>">
            <label for="adresse2" class="<?php echo $class_label; ?> col-form-label"><?php __('Adresse 2') ; ?>
                <i class="fas fa-info-circle" title="<?php __('Lieu-dit, zone d’activité, BP (pour boite postale)…') ; ?>"></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="adresse2" value="<?php echo htmlentities(@$post['adresse2']); ?>" placeholder="<?php echo htmlentities(_('Complément d\'adresse')) ; ?>">
            </div>
        </div>
        <?php } ?>
        <?php if ( in_array('a3', $show) ) { ?>
        <div class="<?= $class_line ;?>">
            <label for="adresse3" class="<?php echo $class_label; ?> col-form-label"><?php __('Adresse 3') ; ?>
                <i class="fas fa-info-circle" title="<?php __('Niveau de la station et/ou le quartier si nécessaire. Exemple : Morillon village et Morillon 1100.') ; ?>"></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="adresse3" value="<?php echo htmlentities(@$post['adresse3']); ?>" placeholder="<?php echo htmlentities(_('Complément d\'adresse')) ; ?>">
            </div>
        </div>
        <?php } ?>
        <?php
        
        $communes = null;
        if (isset($_GET['communes'])) {
            $communes = $apidaeEvent->getCommunesByInsee(explode(',', $_GET['communes']));
        } elseif (isset($configApidaeEvent['communes_insee'])) {
            $communes = $apidaeEvent->getCommunesByInsee(explode(',', $configApidaeEvent['communes_insee']));
        } elseif (isset($configApidaeEvent['territoire'])) {
            $communes = $apidaeEvent->getCommunesByTerritoire($configApidaeEvent['territoire'], isset($_GET['refresh']));
        }

        if (!is_array($communes) || sizeof($communes) == 0) {
            //$apidaeEvent->alerte('Liste communes introuvable',$_GET) ;
        ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation"></i>
                <strong><?php __('Impossible de récupérer la liste de communes...') ; ?></strong>
                <br><?php __('Veuillez nous excuser pour la gène occasionnée.') ; ?>
                <br><?php __('Vous pouvez prendre contact avec l\'<a href="https://www.apidae-tourisme.com/apidae-tourisme/carte-du-reseau/" target="_blank">Office du Tourisme concernée par votre manifestation</a>.') ; ?>
            </div>
        <?php
            die();
        }

        @uasort($communes, function ($a, $b) {
            $unwanted_array = array(
                'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
                'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
                'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
                'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
                'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y'
            );
            return strtr($a['nom'], $unwanted_array) > strtr($b['nom'], $unwanted_array);
        });

        ?>
        <div class="<?= $class_line ; ?> required">
            <label for="commune" class="<?php echo $class_label; ?> col-form-label"><?php __('Commune') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <select name="commune" class="select2" required="required" data-placeholder="<?php echo htmlentities(_('Sélectionnez une commune')) ; ?>">
                    <?php if (sizeof($communes) > 1) { ?>
                        <option value=""></option>
                    <?php } ?>
                    <?php

                    foreach ($communes as $d) {
                        $cle = $d['id'] . '|' . $d['codePostal'] . '|' . $d['nom'] . '|' . $d['code'];
                        echo '<option value="' . htmlentities($cle) . '"';
                        if (@$post['commune'] == $cle) echo ' selected="selected"';
                        echo '>';
                        echo $d['nom'];
                        echo ' - ' . $d['codePostal'];
                        if (isset($d['complement']) && $d['complement'] != '') echo ' (' . $d['complement'] . ')';
                        echo '</option>';
                    }

                    ?>
                </select>
            </div>
        </div>

        <?php if ( in_array('lieu', $show) ) { ?>
        <div class="alert alert-info d-flex align-items-stretch" role="alert" style="margin-top:18px;margin-bottom:0 ;">
            <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <div>
                <?php __('Saisir le lieu précis où se déroule l’événement seulement si nécessaire (si l\'adresse n\'est pas suffisante).
                    Ex : Espace culturel / Place du village / Salle des fêtes / Esplanade du lac...') ; ?>
            </div>
        </div>

        <div class="<?= $class_line ; ?>">
            <label for="lieu" class="<?php echo $class_label; ?> col-form-label"><?php __('Lieu précis') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="lieu" value="<?php echo htmlentities(@$post['lieu']); ?>" id="lieu">
            </div>
        </div>
        <?php } ?>

    </fieldset>

    <fieldset>

        <div class="cardHeader">
            <legend><i class="fa-regular fa-calendar" style="font-size:.8em;"></i> <?php __('Dates de la manifestation') ; ?></legend>
            <p><?php __('Indiquez les dates de votre événement') ; ?></p>
        </div>

        <div class="alert alert-warning d-flex align-items-stretch" role="alert">
            <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <div>
                <?php __('Merci de préciser au minimum une date.') ; ?>
            </div>
        </div>

    <?php
        if ( isset($_GET['horaires']) || isset($_GET['apihours']) ) {
            include(realpath(dirname(__FILE__)).'/form.horaires.inc.php') ;
        }
        else {
            include(realpath(dirname(__FILE__)).'/form.dates.inc.php') ;
        }
    ?>

    </fieldset>

    <fieldset>

        <div class="cardHeader">
            <legend><?php __('Description de votre manifestation') ; ?></legend>
        </div>

        <div class="row">

            <?php
                $nbCol = 0 ;
                if ( in_array('type', $show) ) $nbCol++ ;
                if ( in_array('cat', $show) ) $nbCol++ ;
                if ( in_array('theme', $show) ) $nbCol++ ;
                if ( in_array('gen', $show) ) $nbCol++ ;

                $classCol = 'col-sm-6';
                if ($nbCol == 3)
                    $classCol = 'col-sm-4';
                if ($nbCol == 1)
                    $classCol = '';
            ?>

            <?php if ( in_array('type', $show) ) { ?>
            <div class="field <?php echo $classCol ; ?>">
                <label class="<?php echo $class_label; ?> col-form-label"><?php __('Type de manifestation') ; ?></label>
                <div class="<?php echo $class_champ; ?>">
                    <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationType', [
                        'presentation' => 'select',
                        'type' => 'unique',
                        'placeholder' => 'Sélectionnez un type'
                    ], @$post['FeteEtManifestationType']); ?>
                </div>
            </div>
            <?php } ?>

            <?php if ( in_array('cat', $show) ) { ?>
            <?php $limitCategories = 3 ; ?>
            <?php if ( isset($_GET['limitCategories']) && is_int($limitCategories) ) $limitCategories = (int)$_GET['limitCategories'] ; ?>
            <div class="field <?php echo $classCol ; ?>">
                <label class="<?php echo $class_label; ?> col-form-label"><?php
                if ( $limitCategories > 1 ) {
                    __('Catégories de manifestation') ;
                    echo '<small class="sub">'.$limitCategories.' '._(' maximum').'</small>' ;
                } else {
                    __('Catégorie de manifestation') ;
                } ?></label>
                <div class="<?php echo $class_champ; ?>">
                    <?php if ( $limitCategories > 1 ) { ?>
                        <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationCategorie', array('presentation' => 'select', 'maximum_selection_length' => $limitCategories, 'exclude' => $categorie_exclude), @$post['FeteEtManifestationCategorie']); ?>
                    <?php } else  { ?>
                        <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationCategorie', array('presentation' => 'select', 'type' => 'unique', 'exclude' => $categorie_exclude), @$post['FeteEtManifestationCategorie']); ?>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <?php if (in_array('theme', $show)) { ?>
                <div class="field <?php echo $classCol ; ?>">
                    <label class="<?php echo $class_label; ?> col-form-label"><?php __('Thèmes de manifestation'); ?></label>
                    <div class="<?php echo $class_champ; ?>">
                        <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationTheme', array('presentation' => 'select', 'exclude' => $theme_exclude), @$post['FeteEtManifestationTheme']); ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (in_array('gen', $show)) { ?>
                <div class="field <?php echo $classCol ; ?>">
                    <?php
                    $params_generique = [
                        'presentation' => 'select',
                        'type' => 'unique',
                        'include' => [5948, 2392, 5134, 6501, 3726, 2396, 2412, 4963, 4967, 4964, 4965, 4966, 4565, 2421, 6329, 3911, 2384, 3721, 2386, 5627, 2399, 4145, 2397, 6497, 2429, 2383, 4655, 3756, 5490, 5885, 4052, 2385, 2405, 2395, 6500, 2428, 2425, 4997, 4856, 2427, 4998, 5046, 2406, 2387, 2422, 5945, 2403, 2388, 4047, 2423, 4051, 4913, 4146, 4525, 5860, 6457, 2414, 2398, 5321, 6280, 5380, 2401, 2402, 4070, 4574, 2408, 5745, 2503, 4636, 4656, 2426, 2404, 2424, 2411, 2415, 2400, 4572, 2394, 2391, 2389, 2390, 4654, 2407, 7114, 7224, 7249, 7559]
                    ];
                    ?>
                    <label class="<?php echo $class_label; ?> col-form-label"><?php __('Evénements génériques et championnats') ; ?></label>
                    <div class="<?php echo $class_champ; ?>">
                        <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationGenerique', $params_generique, @$post['FeteEtManifestationGenerique']); ?>
                    </div>
                </div>
            <?php } ?>

        </div>

        <div class="field required">
            <label class="<?php echo $class_label; ?> col-form-label th" for="descriptifCourt"><?php __('Descriptif court') ; ?>
                <i class="fas fa-info-circle" title="<?php __('Texte d\'accroche permettant de comprendre la nature de votre prestation. Ne doit pas contenir d\'horaire, de tarif, d\'info de réservation, de N° de tél, de lieu... puisque ces informations existent par ailleurs, ce qui constitue une double saisie.') ; ?>"></i>
                <br><small class="form-text text-muted"><?php __('255 caractères max.') ; ?></small>
            </label>
            <textarea class="form-control" name="descriptifCourt" id="descriptifCourt" maxlength="255" required="required"><?php echo htmlspecialchars(@$post['descriptifCourt']); ?></textarea>
        </div>

        <?php if ( in_array('dd', $show) ) { ?>
        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label th" for="descriptifDetaille"><?php __('Descriptif détaillé') ; ?>
                <i class="fas fa-info-circle" title="<?php __('Le descriptif détaillé est complémentaire du descriptif court et non redondant. En effet certains sites web affichent ces deux champs à la suite.') ; ?>"></i>
            </label>
            <textarea class="form-control" name="descriptifDetaille" id="descriptifDetaille"><?php echo htmlspecialchars(@$post['descriptifDetaille']); ?></textarea>
        </div>
        <?php } ?>

        <?php if (in_array('animaux', $show)) { ?>
        <div class="field">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="animauxAcceptes" id="animauxAcceptes" value="1" <?php if (@$post['animauxAcceptes'] == 1) echo ' checked="checked" '; ?> >
                <label class="<?php echo $class_label; ?> col-form-label form-check-label" for="animauxAcceptes"><?php __('Animaux acceptés') ; ?></label>
            </div>
        </div>

        <div class="field">
            <label class="<?php echo $class_label; ?> col-form-label" for="descriptifAnimauxAcceptes"><?php __('Conditions d\'accueil des animaux') ; ?>
                <i class="fas fa-info-circle" title="<?php __('Animaux acceptés en laisse uniquement, en terrasse uniquement...') ; ?>"></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="descriptifAnimauxAcceptes" id="descriptifAnimauxAcceptes"><?php echo htmlspecialchars(@$post['descriptifAnimauxAcceptes']); ?></textarea>
            </div>
        </div>
        <?php } ?>

        <?php include(realpath(dirname(__FILE__)).'/form.clientele.inc.php') ; ?>

        <?php include(realpath(dirname(__FILE__)).'/form.handicap.inc.php') ; ?>

    </fieldset>

    <?php include(realpath(dirname(__FILE__)).'/form.mc.inc.php') ; ?>

    <?php if (in_array('resa', $show)) { ?>

        <fieldset>

            <div class="cardHeader">
                <legend><?php __('Réservation') ; ?></legend>
            </div>

        <div class="alert alert-warning d-flex align-items-stretch" role="alert">
            <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <div>
                <?php __('Merci de préciser au moins une adresse mail (de préférence) et/ou un numéro de téléphone</strong> : en cas de questions, nous pourrons prendre contact avec l\'organisateur grâce à ces informations.') ; ?>
            </div>
        </div>

            <div class="<?= $class_line ; ?>">
                <label for="reservation_nom" class="<?php echo $class_label; ?> col-form-label"><?php __('Nom de l\'organisme') ; ?></label>
                <div class="<?php echo $class_champ; ?>">
                    <input class="form-control" type="text" name="reservation[nom]" id="reservation_nom" value="<?php echo htmlentities(@$post['reservation']['nom']); ?>">
                </div>
            </div>

            <div class="<?= $class_line ; ?>">
                <label for="reservation_url" class="<?php echo $class_label; ?> col-form-label"><?php __('URL de réservation') ; ?></label>
                <div class="<?php echo $class_champ; ?>">
                    <input class="form-control url" type="text" name="reservation[url]" id="reservation_url" value="<?php echo htmlentities(@$post['reservation']); ?>" placeholder="https://...">
                </div>
            </div>

        </fieldset>

    <?php } ?>

    <?php include(realpath(dirname(__FILE__)).'/form.contacts.inc.php') ; ?>

    <?php include(realpath(dirname(__FILE__)).'/form.tarifs.inc.php') ; ?>

    <?php include(realpath(dirname(__FILE__)).'/form.multimedias.inc.php') ; ?>

    <fieldset>

        <div class="cardHeader">
            <legend><?php __('Organisateur') ; ?></legend>
        </div>

        <div class="alert alert-info d-flex align-items-stretch" role="alert">
            <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <div>
                <?php __('Message privé non publié à destination du propriétaire de ce formulaire. Précisez l\'organisateur de la manifestation (association ABC...).') ; ?>
            </div>
        </div>
        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label th" for="commentaire"><?php __('Commentaire privé') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="commentaire" id="commentaire" placeholder="<?php echo htmlentities(__('Votre message pour l\'organisateur...')) ; ?>"><?php echo htmlspecialchars(@$post['commentaire']); ?></textarea>
            </div>
        </div>
    </fieldset>

    <?php if ($configApidaeEvent['debug']) { ?>
        <div class="<?= $class_line ; ?>">
            <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" name="nosave" id="nosave" value="1" <?php if (@$post['nosave'] == 1) echo ' checked="checked" '; ?> >
                [Debug] Ne pas enregistrer sur Apidae
            </div>
        </div>
        <div class="<?= $class_line ; ?>">
            <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" name="nomail" id="nomail" value="1" <?php 
                    if (@$post['nomail'] == 1 || @$configApidaeEvent['env'] !== 'prod' ) echo ' checked ';
                    if ( $configApidaeEvent['env'] !== 'prod' ) echo ' disabled' ;
                ?> > [Debug] Ne pas envoyer les mails (même pas à admin)
            </div>
        </div>
    <?php } ?>

    <div class="<?= $class_line ; ?> form-check required rgpd">
        <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" class="form-check-input" name="rgpd" id="rgpd" value="1" required="required" <?php if (@$post['rgpd'] == 1) echo ' checked="checked" '; ?> >
            <label for="rgpd" class="form-check-label"><a class="link-secondary" href="https://www.apidae-tourisme.com/charte-de-confidentialite-pour-les-profils-references" target="_blank"><?php __('J\'accepte les conditions RGPD du réseau Apidae') ; ?></a>.</label>
        </div>
    </div>

    <input type="hidden" name="script_uri" value="<?php echo htmlentities(@$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI']); ?>">

    <div class="text-center" <?php
                            if (@$configApidaeEvent['recaptcha_secret'] != '' && !$configApidaeEvent['debug']) echo ' style="display:none;"';
                            ?>>
        <input type="button" class="btn btn-dark btn-lg btn-block btn-submit" value="<?php __('Enregistrer cet événement') ; ?>">
    </div>

    <?php if (@$configApidaeEvent['recaptcha_secret'] != '' && !$configApidaeEvent['debug']) { ?>
        <div class="form-group" id="recaptcha">
            <div class="g-recaptcha" data-sitekey="<?php echo $configApidaeEvent['recaptcha_sitekey']; ?>" data-callback="recaptchaOk" data-expired-callback="recaptchaKo"></div>
            <p><?php __('Vous devez cocher la case "Je ne suis pas un robot" pour pouvoir enregistrer') ; ?></p>
        </div>
    <?php } ?>

</form>
