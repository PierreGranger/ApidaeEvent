<?php
        $nb = 1;
        if (isset($post['date'])) $nb = sizeof($post['date']);
        ?>

        <div class="dates-rows" data-row-selector=".date-row">
            <?php for ($i = 0; $i < $nb; $i++) { ?>
                <div class="row date-row">

                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label required th"><?php __('Début') ; ?></label>
                            <div class="input-group date">
                                <input
                                    class="form-control date"
                                    type="date"
                                    min="<?= date('Y-m-d') ; ?>"
                                    name="date[<?= $i ; ?>][debut]"
                                    value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>"
                                    required="required"
                                    autocomplete="chrome-off"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label required th"><?php __('Fin') ; ?></label>
                            <div class="input-group date">
                                <input
                                    class="form-control date"
                                    type="date"
                                    min="<?= date('Y-m-d') ; ?>"
                                    name="date[<?= $i ; ?>][fin]"
                                    value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>"
                                    required="required"
                                    autocomplete="chrome-off"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Heure début') ; ?></label>
                            <div class="input-group time">
                                <input
                                    class="form-control time"
                                    type="time"
                                    name="date[<?= $i ; ?>][hdebut]"
                                    value="<?= htmlentities(@$post['date'][$i]['hdebut']) ; ?>"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Heure fin') ; ?></label>
                            <div class="input-group time">
                                <input
                                    class="form-control time"
                                    type="time"
                                    name="date[<?= $i ; ?>][hfin]"
                                    value="<?= htmlentities(@$post['date'][$i]['hfin']) ; ?>"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Complément') ; ?></label>
                            <input
                                class="form-control"
                                type="text"
                                name="date[<?= $i ; ?>][complementHoraire]"
                                value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>"
                                placeholder="<?php echo htmlentities((_('Précisions'))) ?>"
                            />
                        </div>
                    </div>

                    <div class="moins"><?php if ($i > 0) echo $icon_moins; ?></div>

                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-12 dates-plus" data-rows-container=".dates-rows" data-row-selector=".date-row">
                <?= preg_replace('/##LIBELLE##/', __('Ajouter une date',false), $icon_plus) ; ?>
            </div>
        </div>

        <div class="row errors">
            <div class="col-12 dates-errors"></div>
        </div>