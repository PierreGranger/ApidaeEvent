<?php

    $class_line = 'row mb-2' ;
    $class_label = 'col-sm-2';
    $class_champ = 'col-sm-10';

?>

<form class="form" method="post" enctype="multipart/form-data" novalidate>

    <?php $referer = (isset($_POST['referer'])) ? $_POST['referer'] : @$_SERVER['HTTP_REFERER']; ?>
    <input type="hidden" name="referer" value="<?php echo htmlentities($referer); ?>" />
    <input type="hidden" name="devise" value="<?php echo htmlentities($devise_apidae); ?>" />

    <fieldset class="form-group required">
        <legend>Nom de la manifestation</legend>
        <div class="controls">
            <input class="form-control form-control-lg" name="nom" type="text" value="<?php echo htmlentities(@$post['nom']); ?>" id="nom" required="required" />
        </div>
    </fieldset>

    <fieldset>

        <legend>Importance de votre événement</legend>

        <div class="<?= $class_line ; ?>">
            <label for="portee" class="<?= $class_label; ?> col-form-label"> Portée <i class="fas fa-info-circle" title="La portée concerne les spectateurs et la distance qu’ils sont prêt à parcourir pour participer à une manifestation."></i></label>
            <div class="<?= $class_champ ; ?>">
                <select class="form-control" name="portee" id="portee" required="required">
                    <option value="">-</option>
                    <?php

                    $FeteEtManifestationPortees = $apidaeEvent->getElementsReferenceByType('FeteEtManifestationPortee');
                    foreach ($FeteEtManifestationPortees as $option) {
                        echo '<option value="' . $option['id'] . '"';
                        if (isset($option['description'])) echo ' title="' . htmlspecialchars($option['description']) . '" ';
                        if (isset($post['portee']) && $post['portee'] == $option['id']) echo ' selected="selected"';
                        echo '>' . $option['libelleFr'] . '</option>';
                    }

                    ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6 row">
                <label for="nbParticipantsAttendu" class="col-sm-4 col-form-label">Participants attendus</label>
                <div class="col-sm-8">
                    <input class="form-control" type="number" name="nbParticipantsAttendu" id="nbParticipantsAttendu" value="<?php echo htmlentities(@$post['nbParticipantsAttendu']); ?>" />
                </div>
            </div>
            <div class="col-sm-6 row">
                <label for="nbVisiteursAttendu" class="col-sm-4 col-form-label">Visiteurs attendus</label>
                <div class="col-sm-8">
                    <input class="form-control" type="number" name="nbVisiteursAttendu" id="nbVisiteursAttendu" value="<?php echo htmlentities(@$post['nbVisiteursAttendu']); ?>" />
                </div>
            </div>
        </div>
        
    </fieldset>

    <fieldset>
        <legend>Adresse</legend>
        <div class="<?= $class_line ;?>">
            <label for="adresse1" class="<?php echo $class_label; ?> col-form-label">Adresse 1
                <i class="fas fa-info-circle" title="Voie et bâtiment. Exemple : 60 rue des Lilas - Bâtiment A. Pas de virgule mais un espace entre le numéro et le nom de la rue."></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="adresse1" value="<?php echo htmlentities(@$post['adresse1']); ?>" />
            </div>
        </div>
        <div class="<?= $class_line ;?>">
            <label for="adresse2" class="<?php echo $class_label; ?> col-form-label">Adresse 2
                <i data-bs-toggle="tooltip" class="fas fa-info-circle" title="Lieu-dit, zone d’activité, BP (pour boite postale)…"></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="adresse2" value="<?php echo htmlentities(@$post['adresse2']); ?>" />
            </div>
        </div>
        <div class="<?= $class_line ;?>">
            <label for="adresse3" class="<?php echo $class_label; ?> col-form-label">Adresse 3
                <i class="fas fa-info-circle" title="Niveau de la station et/ou le quartier si nécessaire. Exemple : Morillon village et Morillon 1100."></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="adresse3" value="<?php echo htmlentities(@$post['adresse3']); ?>" />
            </div>
        </div>
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
                <strong>Impossible de récupérer la liste de communes...</strong>
                <br />Veuillez nous excuser pour la gène occasionnée.
                <br />Vous pouvez prendre contact avec l'<a href="https://www.apidae-tourisme.com/apidae-tourisme/carte-du-reseau/" target="_blank">Office du Tourisme concernée par votre manifestation</a>.
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
            <label for="commune" class="<?php echo $class_label; ?> col-form-label">Commune</label>
            <div class="<?php echo $class_champ; ?>">
                <select name="commune" class="chosen-select" required="required" data-placeholder="">
                    <?php if (sizeof($communes) > 1) { ?>
                        <option value="">-</option>
                    <?php } ?>
                    <?php

                    foreach ($communes as $d) {
                        $cle = $d['id'] . '|' . $d['codePostal'] . '|' . $d['nom'] . '|' . $d['code'];
                        echo '<option value="' . htmlentities($cle) . '"';
                        if (@$post['commune'] == $cle) echo ' selected="selected"';
                        echo '>';
                        echo $d['nom'];
                        //if ( isset($_GET['devise']) && $_GET['devise'] == 'CHF' ) echo ' - ' . $d['complement'] ;
                        echo ' - ' . $d['codePostal'];
                        if (isset($d['complement']) && $d['complement'] != '') echo ' (' . $d['complement'] . ')';
                        echo '</option>';
                    }

                    ?>
                </select>
            </div>
        </div>

        <div class="alert alert-info" role="alert">
            <p>Saisir le lieu précis où se déroule l’événement <strong>seulement si nécessaire</strong> (si l'adresse n'est pas suffisante).<br />
                Ex : Espace culturel / Place du village / Salle des fêtes / Esplanade du lac...</p>
        </div>

        <div class="<?= $class_line ; ?>">
            <label for="lieu" class="<?php echo $class_label; ?> col-form-label">Lieu précis</label>
            <div class="<?php echo $class_champ; ?>">
                <input class="form-control" type="text" name="lieu" value="<?php echo htmlentities(@$post['lieu']); ?>" id="lieu">
            </div>
        </div>

    </fieldset>

    <?php
        if ( isset($_GET['apihours']) ) {
            include(realpath(dirname(__FILE__)).'/form.apihours.inc.php') ;
        }
        else {
            include(realpath(dirname(__FILE__)).'/form.dates.inc.php') ;
        }
    ?>

    <fieldset>

        <legend>Description de votre manifestation</legend>

        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label">Type de manifestation</label>
            <div class="<?php echo $class_champ; ?>">
                <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationType', array('presentation' => 'select', 'type' => 'unique'), @$post['FeteEtManifestationType']); ?>
            </div>
        </div>

        <?php $limitCategories = 3 ; ?>
        <?php if ( isset($_GET['limitCategories']) && is_int($limitCategories) ) $limitCategories = (int)$_GET['limitCategories'] ; ?>
        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label">Catégorie<?php $limitCategories > 1 ? 's':'' ; ?> de manifestation</label>
            <div class="<?php echo $class_champ; ?>">
                <?php if ( $limitCategories > 1 ) { ?>
                    <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationCategorie', array('presentation' => 'select', 'max_selected_options' => $limitCategories, 'exclude' => $categorie_exclude), @$post['FeteEtManifestationCategorie']); ?>
                    <small class="form-text text-muted"><?php echo $limitCategories ; ?> catégories maximum</small>
                <?php } else  { ?>
                    <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationCategorie', array('presentation' => 'select', 'type' => 'unique', 'exclude' => $categorie_exclude), @$post['FeteEtManifestationCategorie']); ?>
                <?php } ?>
            </div>
        </div>

        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label">Thèmes de manifestation</label>
            <div class="<?php echo $class_champ; ?>">
                <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationTheme', array('presentation' => 'select', 'exclude' => $theme_exclude), @$post['FeteEtManifestationTheme']); ?>
            </div>
        </div>

        <?php if (isset($_GET['generique'])) { ?>
            <?php
            $params_generique = [
                'presentation' => 'select',
                'type' => 'unique',
                'include' => [5948, 2392, 5134, 6501, 3726, 2396, 2412, 4963, 4967, 4964, 4965, 4966, 4565, 2421, 6329, 3911, 2384, 3721, 2386, 5627, 2399, 4145, 2397, 6497, 2429, 2383, 4655, 3756, 5490, 5885, 4052, 2385, 2405, 2395, 6500, 2428, 2425, 4997, 4856, 2427, 4998, 5046, 2406, 2387, 2422, 5945, 2403, 2388, 4047, 2423, 4051, 4913, 4146, 4525, 5860, 6457, 2414, 2398, 5321, 6280, 5380, 2401, 2402, 4070, 4574, 2408, 5745, 2503, 4636, 4656, 2426, 2404, 2424, 2411, 2415, 2400, 4572, 2394, 2391, 2389, 2390, 4654, 2407]
            ];
            ?>
            <div class="<?= $class_line ; ?>">
                <label class="<?php echo $class_label; ?> col-form-label">Evénements génériques et championnats</label>
                <div class="<?php echo $class_champ; ?>">
                    <?php echo $apidaeEvent->formHtmlCC('FeteEtManifestationGenerique', $params_generique, @$post['FeteEtManifestationGenerique']); ?>
                </div>
            </div>
        <?php } ?>

        <div class="<?= $class_line ; ?> required">
            <label class="<?php echo $class_label; ?> col-form-label" for="descriptifCourt">Descriptif court
                <i class="fas fa-info-circle" title="Texte d'accroche permettant de comprendre la nature de votre prestation. Ne doit pas contenir d'horaire, de tarif, d'info de réservation, de N° de tél, de lieu... puisque ces informations existent par ailleurs, ce qui constitue une double saisie."></i>
                <br /><small class="form-text text-muted">255 caractères max.</small>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="descriptifCourt" id="descriptifCourt" maxlength="255" required="required"><?php echo htmlspecialchars(@$post['descriptifCourt']); ?></textarea>
            </div>
        </div>

        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label" for="descriptifDetaille">Descriptif détaillé
                <i class="fas fa-info-circle" title="Le descriptif détaillé est complémentaire du descriptif court et non redondant. En effet certains sites web affichent ces deux champs à la suite."></i>
            </label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="descriptifDetaille" id="descriptifDetaille"><?php echo htmlspecialchars(@$post['descriptifDetaille']); ?></textarea>
            </div>
        </div>

        <?php include(realpath(dirname(__FILE__)).'/form.clientele.inc.php') ; ?>

        <?php include(realpath(dirname(__FILE__)).'/form.handicap.inc.php') ; ?>

    </fieldset>

    <?php include(realpath(dirname(__FILE__)).'/form.mc.inc.php') ; ?>

    <?php if (isset($_GET['reservation']) && $_GET['reservation']) { ?>

        <fieldset>

            <legend>Réservation</legend>

            <div class="<?= $class_line ; ?>">
                <label for="reservation_nom" class="<?php echo $class_label; ?> col-form-label">Nom de l'organisme</label>
                <div class="<?php echo $class_champ; ?>">
                    <input class="form-control" type="text" name="reservation[nom]" id="reservation_nom" value="<?php echo htmlentities(@$post['reservation']['nom']); ?>">
                </div>
            </div>

            <div class="<?= $class_line ; ?>">
                <label for="reservation_url" class="<?php echo $class_label; ?> col-form-label">URL de réservation<br /><small>http(s)://...</small></label>
                <div class="<?php echo $class_champ; ?>">
                    <input class="form-control url" type="text" name="reservation[url]" id="reservation_url" value="<?php echo htmlentities(@$post['reservation']); ?>" placeholder="https://...">
                    <small class="helper url">http(s)://...</small>
                </div>
            </div>

        </fieldset>

    <?php } ?>

    <?php include(realpath(dirname(__FILE__)).'/form.contacts.inc.php') ; ?>

    <?php include(realpath(dirname(__FILE__)).'/form.tarifs.inc.php') ; ?>

    <?php include(realpath(dirname(__FILE__)).'/form.multimedias.inc.php') ; ?>

    <fieldset>
        <legend>Organisateur</legend>
        <div class="alert alert-info" role="alert">
            Vous pouvez laisser un message ci-dessous : il sera communiqué à votre office de tourisme, mais ne sera pas publié.<br />
            Merci de préciser <strong>l'organisateur de la manifestation</strong> (association ABC...).
        </div>
        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label" for="commentaire">Commentaire privé</label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="commentaire" id="commentaire"><?php echo htmlspecialchars(@$post['commentaire']); ?></textarea>
            </div>
        </div>
    </fieldset>

    <?php if ($configApidaeEvent['debug']) { ?>
        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label" for="nosave">[Debug] Ne pas enregistrer sur Apidae</label>
            <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" name="nosave" id="nosave" value="1" <?php if (@$post['nosave'] == 1) echo ' checked="checked" '; ?> />
            </div>
        </div>
        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label" for="nomail">[Debug] Ne pas envoyer les mails (même pas à admin)</label>
            <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" name="nomail" id="nomail" value="1" <?php if (@$post['nomail'] == 1) echo ' checked="checked" '; ?> />
            </div>
        </div>
    <?php } ?>

    <input type="hidden" name="script_uri" value="<?php echo htmlentities(@$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI']); ?>" />

    <div class="form-group" <?php
                            if (@$configApidaeEvent['recaptcha_secret'] != '' && !$configApidaeEvent['debug']) echo ' style="display:none;"';
                            ?>>
        <input type="button" class="btn btn-success btn-lg btn-block btn-submit" value="Enregistrer cet événement" />
    </div>

    <?php if (@$configApidaeEvent['recaptcha_secret'] != '' && !$configApidaeEvent['debug']) { ?>
        <div class="form-group" id="recaptcha">
            <div class="g-recaptcha" data-sitekey="<?php echo $configApidaeEvent['recaptcha_sitekey']; ?>" data-callback="recaptchaOk" data-expired-callback="recaptchaKo"></div>
            <p>Vous devez cocher la case "Je ne suis pas un robot" pour pouvoir enregistrer</p>
        </div>
    <?php } ?>

    <div style="text-align:center;padding:40px ;">
        <?php if ( strtotime(date('Y-m-d')) < strtotime('2022-07-05') ) { ?>
        <a href="https://www.apidae-tourisme.com" target="_blank"><img src="./logo.png" alt="Apidae Event" width="170" /></a>
        <?php } else { ?>
            <a href="https://www.apidae-tourisme.com" target="_blank"><img src="./Apidae_Event.png" alt="Apidae Event" width="170" /></a>
        <?php } ?>
    </div>

</form>
