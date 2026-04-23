<fieldset class="contacts<?php if (isset($_GET['contactObligatoire']) && $_GET['contactObligatoire']) echo ' required'; ?>">

    <div class="cardHeader">
        <legend><?php __('Contacts organisateurs') ; ?></legend>
    </div>

    <div class="alert alert-warning d-flex align-items-stretch" role="alert">
        <div class="d-flex align-items-center" style="padding:0 15px 0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
        <div>
            <?php __('Merci de préciser au moins une adresse mail et/ou un numéro de téléphone.') ; ?>
        </div>
    </div>

        <?php $types = $apidaeEvent->getElementsReferenceByType('ContactFonction', [
            'exclude' => [
                469, // Maire
                460, // Présidence
                466, // Presse
                464, // Propriétaire des murs
                465, // Propriétaire du fonds
                3645, // Référent handicap
                6865, // Référent Taxe de séjour
                4121, // Remise des clés
            ]
        ]); ?>

        <div class="contacts-rows" data-row-selector=".contact-row">
            <?php for ($i = 0; $i < 1; $i++) { ?>
                <div class="row contact-row">
                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Fonction') ; ?></label>
                            <select class="form-select" name="contact[<?= $i ; ?>][fonction]">
                                <option value="">-</option>
                                <?php foreach ($types as $type) { ?>
                                    <option value="<?= $type['id']; ?>"<?php if (@$post['contact'][$i]['fonction'] == $type['id']) echo ' selected="selected" '; ?>><?= $apidaeEvent->libelleEr($type); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Prénom') ; ?></label>
                            <input class="form-control" type="text" name="contact[<?= $i ; ?>][prenom]" value="<?= htmlspecialchars(@$post['contact'][$i]['prenom']) ; ?>">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Nom') ; ?></label>
                            <input class="form-control" type="text" name="contact[<?= $i ; ?>][nom]" value="<?= htmlspecialchars(@$post['contact'][$i]['nom']) ; ?>">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Mail') ; ?></label>
                            <input class="form-control mail" type="text" name="contact[<?= $i ; ?>][mail]" value="<?= htmlspecialchars(@$post['contact'][$i]['mail']) ; ?>" placeholder="xxx@yyyy.zz">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Téléphone') ; ?></label>
                            <input class="form-control telephone" type="text" name="contact[<?= $i ; ?>][telephone]" value="<?= htmlspecialchars(@$post['contact'][$i]['telephone']) ; ?>" placeholder="<?= $phone_placeholder ; ?>">
                        </div>
                    </div>
                    <div class="moins">
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-12 contacts-plus" data-rows-container=".contacts-rows" data-row-selector=".contact-row">
                <?php echo preg_replace('/##LIBELLE##/', __('Ajouter un contact', false), $icon_plus); ?>
            </div>
        </div>

        <div class="row errors">
            <div class="col-12 contacts alert alert-error"></div>
        </div>

    </fieldset>
