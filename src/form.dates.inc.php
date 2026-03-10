<fieldset>

        <div class="cardHeader">
            <legend><i class="fa-regular fa-calendar" style="font-size:.8em;"></i> <?php __('Dates de la manifestation') ; ?></legend>
            <p class="desc"><?php __('Indiquez les dates de votre événement') ; ?></p>
        </div>

        <div class="alert alert-warning" role="alert">
            <div class="float-start" style="padding:0 5px ;"><i class="fa-solid fa-circle-info"></i></div>
            <?php __('Merci de préciser au minimum une date.') ; ?>
        </div>

        <div class="table-responsive">
            <table class="table dates">
                <thead>
                    <tr>
                        <th class="required"><?php __('Début') ; ?></th>
                        <th class="required"><?php __('Fin') ; ?></th>
                        <th><?php __('Heure début') ; ?></th>
                        <th><?php __('Heure fin') ; ?></th>
                        <th><?php __('Complément') ; ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td class="plus" colspan="99"><?= preg_replace('/##LIBELLE##/', __('Ajouter une date',false), $icon_plus) ; ?></td>
                    </tr>
                    <tr class="errors">
                        <td colspan="99"></td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    $nb = 1;
                    if (isset($post['date'])) $nb = sizeof($post['date']);
                    for ($i = 0; $i < $nb; $i++) { ?>
                        <tr>
                            <td>
                                <div class="input-group form-group date">
                                    <input class="form-control date" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group date">
                                    <input class="form-control date" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group time">
                                    <input class="form-control time" type="time" name="date[<?= $i ; ?>][hdebut]" value="<?= htmlentities(@$post['date'][$i]['hdebut']) ; ?>" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group time">
                                    <input class="form-control time" type="time" name="date[<?= $i ; ?>][hfin]" value="<?= htmlentities(@$post['date'][$i]['hfin']) ; ?>" />
                                </div>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" placeholder="<?php echo htmlentities((_('Précisions'))) ?>" />
                            </td>
                        </tr>
                    <?php } ?>
        
                </tbody>
            </table>
        </div>

    </fieldset>