
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

        <?php
        $nb = 1;
        if (isset($post['date'])) $nb = sizeof($post['date']);
        ?>

        <div class="dates-rows" data-row-selector=".date-row">
            <?php for ($i = 0; $i < $nb; $i++) { ?>
                <div class="row date-row">
                    <div class="col-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label required th"><?php __('Début') ; ?></label>
                            <div class="form-floating date">
                                <input class="form-control date debut" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_debut" />
                                <label for="date_<?= $i ; ?>_debut"><?php __('Du') ; ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label required th"><?php __('Fin') ; ?></label>
                            <div class="form-floating date">
                                <input class="form-control date fin" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_fin" />
                                <label for="date_<?= $i ; ?>_fin"><?php __('Au') ; ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Horaires') ; ?></label>
                            <button type="button" class="timePeriods btn btn-primary" onclick="btnTimePeriods()"><?php __('Saisie des horaires') ; ?></button>
                            <input type="hidden" class="timePeriods" name="date[<?= $i ; ?>][timePeriods]" value="<?= htmlentities(@$post['date'][$i]['timePeriods']) ; ?>" />
                            <div class="col-12 description" style="white-space:pre-wrap; font-size:.8em;"></div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="form-group">
                            <label class="col-form-label th"><?php __('Complément') ; ?></label>
                            <div class="form-floating">
                                <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" id="date_complement_<?= $i ; ?>" />
                                <label for="date_<?= $i ; ?>_complement"><?php __('Autres précisions') ; ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="moins"><?php if ($i > 0) echo $icon_moins; ?></div>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-12 dates-plus" data-rows-container=".dates-rows" data-row-selector=".date-row">
                <?= preg_replace('/##LIBELLE##/', __('Ajouter une date', false), $icon_plus) ; ?>
            </div>
        </div>

        <div class="row errors">
            <div class="col-12 dates-errors"></div>
        </div>