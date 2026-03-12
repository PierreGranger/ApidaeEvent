<fieldset>

        <div class="cardHeader">
            <legend><?php __('Moyens de communication') ; ?></legend>
        </div>
        
        <div class="alert alert-warning" role="alert">
            <div class="float-start" style="padding:0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <?php __('Merci de préciser au moins un moyen de communication (Mail, téléphone...) : ils seront diffusés sur les supports de communications (sites web, brochures...)') ; ?>
        </div>

        <?php
        $types = $apidaeEvent->getElementsReferenceByType('MoyenCommunicationType', array('include' => $configApidaeEvent['types_mcs']));
        $nb = 3;
        if (isset($post['mc'])) $nb = sizeof($post['mc']);
        ?>

        <div class="mc-rows" data-row-selector=".mc-row">
            <?php for ($i = 0; $i < $nb; $i++) { ?>
                <div class="row mc-row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label th required"><?php __('Type') ; ?></label>
                            <select class="form-select" name="mc[<?= $i ; ?>][type]">
                                <option value="">-</option>
                                <?php foreach ($types as $type) { ?>
                                    <option value="<?= $type['id']; ?>"
                                        <?php
                                        if (isset($post['mc'])) {
                                            if (@$post['mc'][$i]['type'] == $type['id']) echo ' selected="selected" ';
                                        } else {
                                            if (
                                                ($i == 0 && $type['id'] == 201)
                                                || ($i == 1 && $type['id'] == 204)
                                                || ($i == 2 && $type['id'] == 205)
                                            ) echo ' selected="selected" ';
                                        }
                                        ?>
                                    ><?= $apidaeEvent->libelleEr($type); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label th required"><?php __('Coordonnée') ; ?></label>
                            <input class="form-control" type="text" name="mc[<?= $i ; ?>][coordonnee]" value="<?= htmlentities(@$post['mc'][$i]['coordonnee']) ; ?>" />
                            <small style="display:none;" class="help h205">http(s)://...</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Complément') ; ?></label>
                            <input class="form-control" type="text" name="mc[<?= $i ; ?>][observations]" value="<?= htmlentities(@$post['mc'][$i]['observations']) ; ?>" placeholder="<?=  htmlentities(__('Informations supplémentaires')) ?>" />
                        </div>
                    </div>
                    <div class="moins">
                        <?php if ($i > 0) echo $icon_moins; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-12 mc-plus" data-rows-container=".mc-rows" data-row-selector=".mc-row">
                <?php echo preg_replace('/##LIBELLE##/', __('Ajouter une ligne', false), $icon_plus); ?>
            </div>
        </div>

        <div class="row errors">
            <div class="col-12 mc-errors"></div>
        </div>
    </fieldset>
