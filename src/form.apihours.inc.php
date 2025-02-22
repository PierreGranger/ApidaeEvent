    
    <script type="text/javascript" src="https://form.apihours.apidae-tourisme.<?php echo isset($config['apihours']['env']) ? $config['apihours']['env'] : 'com' ; ?>/0.6.0/bundle.js"></script>

    <script>

        function btnTimePeriods(){
            var btn = event.srcElement
            let start = btn.closest('.row, tr').querySelector('input.debut').value
            let end = btn.closest('.row, tr').querySelector('input.fin').value
            let input = btn.closest('.row, tr').querySelector('input.timePeriods')
            let description = btn.closest('.row, tr').querySelector('div.description')
            if ( start != '' && end != '' ) openApiHours(start,end,input,description)
            else alert('<?php __('Merci de préciser les dates avant de saisir les horaires associés') ; ?>')
        }

        function openApiHours(start, end, input, description) {

            let timeSchedule = {startDate: start, endDate: end, externalType: 'FETE_ET_MANIFESTATION'}
            try { timeSchedule.timePeriods = JSON.parse(input.value) } catch (e) {}
            let nom = document.getElementById('nom').value
            openApiHoursForm(nom, timeSchedule, {
                onSubmit: function(timePeriods) {
                    input.value = timePeriods
                    try { 
                        description.innerHTML = ''
                        var tps = JSON.parse(timePeriods)
                        tps.forEach(timePeriod => description.innerHTML += timePeriod.description + "\n" )
                    } catch (e) {}
                }
            });
        }

    </script>

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
                        <th><?php __('Horaires') ; ?></th>
                        <th><?php __('Complément') ; ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $nb = 1;
                if (isset($post['date'])) $nb = sizeof($post['date']);
                for ($i = 0; $i < $nb; $i++) { ?>
                    <tr>
                        <td></div>
                        <td>
                            <div class="form-floating date">
                                <input class="form-control date debut" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_debut" />
                                <label for="date_<?= $i ; ?>_debut"><?php __('Du') ; ?></label>
                            </div>
                        </td>
                        <td>
                            <div class="form-floating date">
                                <input class="form-control date fin" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_fin" />
                                <label for="date_<?= $i ; ?>_fin"><?php __('Au') ; ?></label>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="timePeriods btn btn-primary" onclick="btnTimePeriods()"><?php __('Saisie des horaires') ; ?></button>
                            <input type="hidden" class="timePeriods" name="date[<?= $i ; ?>][timePeriods]" value="<?= htmlentities(@$post['date'][$i]['timePeriods']) ; ?>" />
                            <div class="col-12 description" style="white-space:pre-wrap; font-size:.8em;"></div>
                        </td>
                        <td>
                            <div class="form-floating">
                                <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" id="date_complement_<?= $i ; ?>" />
                                <label for="date_<?= $i ; ?>_complement"><?php __('Autres précisions') ; ?></label>
                            </div>
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










    <?php return false ; ?>

    <div class="multirows">
            <div class="rows">
                <?php
                $nb = 1;
                if (isset($post['date'])) $nb = sizeof($post['date']);
                for ($i = 0; $i < $nb; $i++) { ?>
                    <div class="row">
                        <div class="col-1"></div>
                        <div class="col-2">
                            <div class="form-floating date">
                                <input class="form-control date debut" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_debut" />
                                <label for="date_<?= $i ; ?>_debut"><?php __('Du') ; ?></label>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-floating date">
                                <input class="form-control date fin" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_fin" />
                                <label for="date_<?= $i ; ?>_fin"><?php __('Au') ; ?></label>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-floating">
                                <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" id="date_complement_<?= $i ; ?>" />
                                <label for="date_<?= $i ; ?>_complement"><?php __('Autres précisions') ; ?></label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="button" class="timePeriods btn btn-primary" onclick="btnTimePeriods()">Saisie des horaires</button>
                            <input type="hidden" class="timePeriods" name="date[<?= $i ; ?>][timePeriods]" value="<?= htmlentities(@$post['date'][$i]['timePeriods']) ; ?>" />
                        <div class="col-12 description" style="white-space:pre-wrap; font-size:.8em;"></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="plus"><?= preg_replace('/##LIBELLE##/', __('Ajouter une date',false), $icon_plus) ; ?></div>
        </div>
