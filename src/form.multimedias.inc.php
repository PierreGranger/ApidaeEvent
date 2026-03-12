<?php
    $classes = ['form-group', 'illustrations'];
    if (isset($_GET['illustrationObligatoire']) && $_GET['illustrationObligatoire']) $classes[] = 'required';
    if (isset($_GET['copyright']) && $_GET['copyright']) $classes[] = 'copyright';
    ?>
    <fieldset class="<?php echo implode(' ', $classes); ?>">

        <div class="cardHeader">
            <legend><?php __('Photos') ; ?></legend>
        </div>

        <div class="alert alert-warning d-flex align-items-stretch" role="alert">
            <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <div class="">
                <?php __('Vos photos doivent être libres de droit et de bonne qualité') ; ?> (
                <?php if (isset($_GET['illustrationMini'])) { ?>
                    <strong><?php echo $_GET['illustrationMini']; ?><?php __('px de largeur minimum') ; ?></strong>
                <?php } else { ?>
                    <?php __('si possible, 1920px de largeur minimum') ; ?>
                <?php } ?> <?php __('et 10 Mo maximum), aux formats png ou jpg/jpeg.') ; ?>
                <?php __('Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...) : assurez-vous d\'avoir tous les droits nécessaires, et précisez le Copyright si besoin.') ; ?>
                <br />
                <a class="alert-link" href="https://aide.apidae-tourisme.com/hc/fr/articles/360000825391-Saisie-l-onglet-multimédias-Zoom-sur-les-illustrations#tailleimages" target="_blank"><?php __('Plus d\'informations ici') ; ?>.</a>
            </div>
        </div>

        <div class="illustrations-rows" data-row-selector=".illustration-row">
            <?php for ($i = 0; $i < 1; $i++) { ?>
                <div class="row illustration-row g-2 row row-cols-2 row-cols-md-3">
                    <div class="col">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Votre photo') ; ?></label>
                            <input class="form-control" type="file" name="illustrations[<?= $i ; ?>]" accept="image/*" <?php if (isset($_GET['illustrationMini']) && (int)$_GET['illustrationMini'] > 0 && (int)$_GET['illustrationMini'] <= 2000) echo 'minwidth="' . (int)$_GET['illustrationMini'] . '" '; ?>/>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Titre') ; ?></label>
                            <input class="form-control" type="text" name="illustrations[<?= $i ; ?>][legende]" value="<?= htmlspecialchars(@$post['illustrations'][$i]['legende']) ; ?>" />
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Copyright') ; ?></label>
                            <input class="form-control" type="text" name="illustrations[<?= $i ; ?>][copyright]" value="<?= htmlspecialchars(@$post['illustrations'][$i]['copyright']) ; ?>" />
                        </div>
                    </div>
                    <div class="moins"></div>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-12 illustrations-plus" data-rows-container=".illustrations-rows" data-row-selector=".illustration-row">
                <?php echo preg_replace('/##LIBELLE##/', __('Ajouter une photo', false), $icon_plus); ?>
            </div>
        </div>

        <div class="row errors">
            <div class="col-12 illustrations-errors"></div>
        </div>
    </fieldset>

    <?php if (isset($_GET['mm']) && $_GET['mm'] == 1) { ?>
        <fieldset class="multimedias">
            
            <div class="cardHeader">
                <legend><?php __('Multimédias') ; ?></legend>
            </div>

            <div class="alert alert-warning" role="alert">
                <div class="float-start" style="padding:0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
                <?php __('Vous pouvez ajouter ci-dessous des fichiers PDF si nécessaire (si vous avez un programme par exemple).
                <br />Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...) : <strong>assurez-vous d\'avoir tous les droits nécessaires</strong>, et précisez le Copyright si besoin.
                <br />Les documents ajoutés ne doivent pas dépasser les 5 Mo au total.') ; ?>
            </div>

            <div class="multimedias-rows" data-row-selector=".multimedia-row">
                <?php for ($i = 0; $i < 1; $i++) { ?>
                    <div class="row multimedia-row g-2 mb-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label th"><?php __('Votre fichier') ; ?></label>
                                <input class="form-control" type="file" name="multimedias[<?= $i ; ?>]" accept="<?= implode(',', $configApidaeEvent['mimes_multimedias']) ; ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label th"><?php __('Titre') ; ?></label>
                                <input class="form-control" type="text" name="multimedias[<?= $i ; ?>][legende]" value="<?= htmlspecialchars(@$post['multimedias'][$i]['legende']) ; ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-form-label th"><?php __('Copyright') ; ?></label>
                                <input class="form-control" type="text" name="multimedias[<?= $i ; ?>][copyright]" value="<?= htmlspecialchars(@$post['multimedias'][$i]['copyright']) ; ?>" />
                            </div>
                        </div>
                        <div class="moins"></div>
                    </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-12 multimedias-plus" data-rows-container=".multimedias-rows" data-row-selector=".multimedia-row">
                    <?php echo preg_replace('/##LIBELLE##/', __('Ajouter un fichier', false), $icon_plus); ?>
                </div>
            </div>

            <div class="row errors">
                <div class="col-12 multimedias-errors"></div>
            </div>
        </fieldset>
    <?php } ?>
