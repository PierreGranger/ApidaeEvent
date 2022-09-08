    
    <script type="text/javascript" src="https://form.apihours-cooking.apidae.net/0.6.0/bundle.js"></script>

    <script>

        function btnTimePeriods(){
            var btn = event.srcElement ;
            let start = btn.closest('tr').querySelector('input.fin').value ;
            let end = btn.closest('tr').querySelector('input.fin').value ;
            let input = btn.closest('tr').querySelector('input.timePeriods') ;
            console.log('openApiHours('+start+','+end+','+input+')') ;
            if ( start != '' && end != '' ) openApiHours(start,end,input) ;
            else alert('@todo verif start/end')
        }

        function openApiHours(start, end, input) {

            let params = {startDate: start, endDate: end, externalType: 'FETE_ET_MANIFESTATION'}
            let nom = document.getElementById('nom').value
            console.log('openApiHoursForm('+nom+','+params+')')
            openApiHoursForm(nom, params, {
            onSubmit: function(timeSchedule) {
                input.value = JSON.stringify(timeSchedule.timePeriods);
            }
            });
        }

    </script>

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
                        <th>Horaires</th>
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
                                    <input class="form-control date debut" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <div class="input-group form-group date">
                                    <input class="form-control date fin" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />
                                </div>
                            </td>
                            <td>
                                <button type="button" class="timePeriods btn btn-primary" onclick="btnTimePeriods()">Saisie des horaires</button>
                                <input type="hidden" class="timePeriods" name="date[<?= $i ; ?>][timePeriods]" value="<?= htmlentities(@$post['date'][$i]['timePeriods']) ; ?>" />
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