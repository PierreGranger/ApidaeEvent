    <fieldset>
        <legend><?php __('Tarifs') ; ?></legend>

        <div class="<?= $class_line ; ?>">
            <label class="<?php echo $class_label; ?> col-form-label" for="gratuit"><?php __('Gratuit pour les visiteurs') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <input type="checkbox" name="gratuit" id="gratuit" value="1" <?php if (@$post['gratuit'] == 1) echo ' checked="checked" '; ?> />
            </div>
        </div>

        <div class="champ tarifs">
            <div class="block">

                <div class="alert alert-warning" role="alert">
                    <p><?php __('<strong>Attention</strong> : chaque type de tarif n\'est utilisable qu\'une fois. Si vous avez plusieurs "pleins tarifs", précisez la plage mini-maxi sur une seule ligne.') ; ?></p>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th><?php __('Type de tarif') ; ?></th>
                                <th><?php echo preg_replace('/#DEVISE#/',$devise_lib,__('Mini #DEVISE# (à partir de...)',false)) ; ?></th>
                                <th><?php echo preg_replace('/#DEVISE#/',$devise_lib,__('Maxi #DEVISE# (jusqu\'à...)',false)) ; ?></th>
                                <th><?php __('Précisions tarifs') ; ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            $types = $apidaeEvent->getElementsReferenceByType('TarifType', array('include' => $configApidaeEvent['types_tarifs']));
                            for ($i = 0; $i < 1; $i++) {
                                    ?>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-select" name="tarifs[<?= $i ; ?>][type]">
                                                    <option value="">-</option>
                                                    <?php foreach ($types as $type) { ?>
                                                        <option value="<?= $type['id'] ; ?>"
                                                            <?php if (@$post['tarifs'][$i]['type'] == $type['id']) echo ' selected="selected" '; ?>
                                                        >
                                                        <?= $apidaeEvent->libelleEr($type); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                <input class="form-control float" type="text" name="tarifs[<?= $i ; ?>][mini]" value="<?= htmlspecialchars(@$post['tarifs'][$i]['mini']) ; ?>" />
                                                <span class="input-group-text"><?= $devise_lib  ; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                                <input class="form-control float" type="text" name="tarifs[<?= $i ; ?>][maxi]" value="<?= htmlspecialchars(@$post['tarifs'][$i]['maxi']) ; ?>" />
                                                <span class="input-group-text"><?= $devise_lib  ; ?></span>
                                            </div>
                                        </td>
                                        <td><input class="form-control" type="text" name="tarifs[<?= $i ; ?>][precisions]" value="<?= htmlspecialchars(@$post['tarifs'][$i]['precisions']) ; ?>" /></td>
                                    </tr>
                                <?php
                            }
                            echo '<tr>';
                            echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', __('Ajouter un tarif',false), $icon_plus) . '</td>';
                            echo '</tr>';
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="<?= $class_line ; ?> complement_tarif">
            <label class="<?php echo $class_label; ?> col-form-label" for="descriptionTarif_complement_<?php echo $libelleXy ; ?>"><?php __('Complément sur les tarifs') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <textarea class="form-control" name="descriptionTarif_complement_<?php echo $libelleXy ; ?>" id="descriptionTarif_complement_<?php echo $libelleXy ; ?>"><?php echo htmlspecialchars(@$post['descriptionTarif_complement_'.$libelleXy]); ?></textarea>
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
            <label class="<?php echo $class_label; ?> col-form-label"><?php __('Modes de paiement') ; ?></label>
            <div class="<?php echo $class_champ; ?>">
                <?php echo $apidaeEvent->formHtmlCC('ModePaiement', $params_paiement, @$post['ModePaiement']); ?>
            </div>
        </div>

    </fieldset>