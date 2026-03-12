    <fieldset>
        
        <div class="cardHeader">
            <legend><?php __('Tarifs') ; ?></legend>
        </div>

        <div class="<?= $class_line ; ?> form-check">
            <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" class="form-check-input" name="gratuit" id="gratuit" value="1" <?php if (@$post['gratuit'] == 1) echo ' checked="checked" '; ?> />
            </div>
            <label class="<?php echo $class_label; ?> form-check-label" for="gratuit"><?php __('Gratuit pour les visiteurs') ; ?></label>
        </div>

        <div class="champ tarifs">
            <div class="block">

                <div class="alert alert-warning d-flex align-items-stretch" role="alert">
                    <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
                    <div>
                        <?php __('Chaque type de tarif n\'est utilisable qu\'une fois. Si vous avez plusieurs "pleins tarifs", précisez la plage mini-maxi.') ; ?>
                    </div>
                </div>

                <?php $types = $apidaeEvent->getElementsReferenceByType('TarifType', array('include' => $configApidaeEvent['types_tarifs'])); ?>
                <div class="tarifs-rows" data-row-selector=".tarif-row">
                    <?php for ($i = 0; $i < 1; $i++) { ?>
                        <div class="row tarif-row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label th"><?php __('Type de tarif') ; ?></label>
                                    <select class="form-select" name="tarifs[<?= $i ; ?>][type]">
                                        <option value="">-</option>
                                        <?php foreach ($types as $type) { ?>
                                            <option value="<?= $type['id'] ; ?>"<?php if (@$post['tarifs'][$i]['type'] == $type['id']) echo ' selected="selected" '; ?>><?= $apidaeEvent->libelleEr($type); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label th"><?php echo preg_replace('/#DEVISE#/', $devise_lib, __('Mini #DEVISE# (à partir de...)', false)); ?></label>
                                    <div class="input-group">
                                        <input class="form-control float" type="text" name="tarifs[<?= $i ; ?>][mini]" value="<?= htmlspecialchars(@$post['tarifs'][$i]['mini']) ; ?>" />
                                        <span class="input-group-text"><?= $devise_lib ; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label th"><?php echo preg_replace('/#DEVISE#/', $devise_lib, __('Maxi #DEVISE# (jusqu\'à...)', false)); ?></label>
                                    <div class="input-group">
                                        <input class="form-control float" type="text" name="tarifs[<?= $i ; ?>][maxi]" value="<?= htmlspecialchars(@$post['tarifs'][$i]['maxi']) ; ?>" />
                                        <span class="input-group-text"><?= $devise_lib ; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label th"><?php __('Précisions tarifs') ; ?></label>
                                    <input class="form-control" type="text" name="tarifs[<?= $i ; ?>][precisions]" value="<?= htmlspecialchars(@$post['tarifs'][$i]['precisions']) ; ?>" placeholder="<?=  htmlentities(__('Détails complémentaires')) ?>" />
                                </div>
                            </div>
                            <div class="moins"></div>
                        </div>
                    <?php } ?>
                </div>

                <div class="row">
                    <div class="col-12 tarifs-plus" data-rows-container=".tarifs-rows" data-row-selector=".tarif-row">
                        <?php echo preg_replace('/##LIBELLE##/', __('Ajouter un tarif', false), $icon_plus); ?>
                    </div>
                </div>

                <div class="row errors">
                    <div class="col-12 tarifs-errors"></div>
                </div>
            </div>
        </div>

        <div class="<?= $class_line ; ?> complement_tarif">
            <label class="<?php echo $class_label; ?> col-form-label th" for="descriptionTarif_complement_<?php echo $libelleXy ; ?>"><?php __('Complément sur les tarifs') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="descriptionTarif_complement_<?php echo $libelleXy ; ?>" id="descriptionTarif_complement_<?php echo $libelleXy ; ?>" placeholder="<?php echo htmlentities(_('Informations complémentaires sur les tarifs...')) ; ?>"><?php echo htmlspecialchars(@$post['descriptionTarif_complement_'.$libelleXy]); ?></textarea>
            </div>
        </div>

        <?php
        $params_paiement = array(
            'presentation' => 'checkbox',
            'exclude' => array(
                1265, // American Express
                1266, // Bons CAF
                //1268, // Carte bancaire/crédit
                1269, // Carte JCB
                1286, // Pass’Région
                //1271, // Chèque
                4136, // Chèque cadeau Gîtes de France
                4139, // Chèques cadeaux
                1284, // Chèque Culture
                1273, // Chèque de voyage
                5646, // Chéquier Jeunes
                //1274, // Chèque Vacances
                1275, // Devise étrangère
                1276, // Diners Club
                //1277, // Espèces
                4098, // Moneo resto
                5408, // Monnaie locale
                //5558, // Paiement en ligne
                1287, // Paypal
                1285, // Titre Restaurant
                1281, // Virement
            )
        );
        ?>
        <div class="<?= $class_line ; ?> modes_paiement">
            <label class="<?php echo $class_label; ?> col-form-label th"><?php __('Modes de paiement') ; ?></label>
            <?php echo $apidaeEvent->formHtmlCC('ModePaiement', $params_paiement, @$post['ModePaiement']); ?>
        </div>

    </fieldset>