<fieldset>

        <legend><?php __('Dates de la manifestation') ; ?></legend>

        <div class="alert alert-warning" role="alert">
            <?php __('Merci de préciser au minimum une date.') ; ?>
        </div>

        <div class="table-responsive">
            <table class="table dates">
                <thead>
                    <tr>
                        <th></th>
                        <th class="required"><?php __('Début') ; ?></th>
                        <th class="required"><?php __('Fin') ; ?></th>
                        <th><?php __('Heure de début') ; ?></th>
                        <th><?php __('Heure de fin') ; ?></th>
                        <th><?php __('Complément') ; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nb = 1;
                    if (isset($post['date'])) $nb = sizeof($post['date']);
                    for ($i = 0; $i < $nb; $i++) { ?>
                        <tr>
                            <td></td>
                            <td>
                                <div class="input-group form-group date">
                                    <input class="form-control date" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group date">
                                    <input class="form-control date" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group time">
                                    <input class="form-control time" type="time" name="date[<?= $i ; ?>][hdebut]" value="<?= htmlentities(@$post['date'][$i]['hdebut']) ; ?>" placeholder="<?php __('hh:mm') ; ?>" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group time">
                                    <input class="form-control time" type="time" name="date[<?= $i ; ?>][hfin]" value="<?= htmlentities(@$post['date'][$i]['hfin']) ; ?>" placeholder="<?php __('hh:mm') ; ?>" />
                                </div>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" />
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="plus" colspan="99"><?= preg_replace('/##LIBELLE##/', __('Ajouter une date',false), $icon_plus) ; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </fieldset>