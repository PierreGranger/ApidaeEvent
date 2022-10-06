<fieldset>

        <legend>Dates de la manifestation</legend>

        <div class="alert alert-warning" role="alert">
            Merci de préciser au minimum une date.
        </div>

        <div class="table-responsive">
            <table class="table dates">
                <thead>
                    <tr>
                        <th></th>
                        <th class="required">Début</th>
                        <th class="required">Fin</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                        <th>Complément</th>
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
                                    <input class="form-control date" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group date">
                                    <input class="form-control date" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group time">
                                    <input class="form-control time" type="time" name="date[<?= $i ; ?>][hdebut]" value="<?= htmlentities(@$post['date'][$i]['hdebut']) ; ?>" placeholder="hh:mm" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group time">
                                    <input class="form-control time" type="time" name="date[<?= $i ; ?>][hfin]" value="<?= htmlentities(@$post['date'][$i]['hfin']) ; ?>" placeholder="hh:mm" />
                                </div>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" />
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="plus" colspan="99"><?= preg_replace('/##LIBELLE##/', 'Ajouter une date', $icon_plus) ; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </fieldset>