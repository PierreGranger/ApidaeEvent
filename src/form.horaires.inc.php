<?php

$nb = 1;

if (isset($post['date'])) {
    $nb = sizeof($post['date']);
}

?>
    <div class="dates-rows" data-row-selector=".date-row">
        <?php for ($i = 0; $i < $nb; $i++) { ?>
            <div class="row date-row">
                <div class="col-6 col-md">
                    <div class="form-group">
                        <label class="col-form-label required th"><?php __('Début') ; ?></label>
                        <input class="form-control date debut" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][debut]" value="<?= htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_debut" />
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="form-group">
                        <label class="col-form-label required th"><?php __('Fin') ; ?></label>
                        <input class="form-control date fin" type="date" min="<?= date('Y-m-d') ; ?>" name="date[<?= $i ; ?>][fin]" value="<?= htmlentities(@$post['date'][$i]['fin']) ; ?>" placeholder="<?php __('jj/mm/aaaa') ; ?>" required="required" autocomplete="chrome-off" id="date_<?= $i ; ?>_fin" />
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="form-group">
                        <label class="col-form-label th"><?php __('Horaires') ; ?></label>
                        <button type="button" class="horaires btn btn-light" onclick="btnHoraires(event)"><?php __('Saisie des horaires') ; ?></button>
                        <input type="hidden" class="horaires" name="date[<?= $i ; ?>][horaires]" value="<?= htmlentities(@$post['date'][$i]['horaires']) ; ?>" />
                        <div class="col-12 description" style="white-space:pre-wrap; font-size:.8em;"></div>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="form-group">
                        <label class="col-form-label th"><?php __('Complément') ; ?></label>
                        <input class="form-control" type="text" name="date[<?= $i ; ?>][complementHoraire]" value="<?= htmlentities(@$post['date'][$i]['complementHoraire']) ; ?>" id="date_complement_<?= $i ; ?>" />
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

<div class="modal fade modal-lg" id="horairesModal" tabindex="-1" aria-labelledby="horairesModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
        <h1 class="modal-title fs-5" id="horairesModalLabel"><?php __('Horaires') ; ?></h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php __('Fermer') ; ?>"></button>
    </div>
    <div class="modal-body">
        <div id="reactGuiContainer"></div>
        <div style="display:none;">
            <ul>
                <li><input id="trIndex" type="text" value="" /> trIndex</li>
                <li><input id="horairesField" type="text" value="[]" /> horairesField</li>
                <li><input id="availableHoraireTypesField" type="text" value="<?php echo htmlspecialchars(json_encode([$apidaeEvent->getElementReferenceByTypeAndName('HoraireType','Ouverture')])) ?>" /> availableHoraireTypesField</li>
                <li><input id="reusablePeriodesField" type="text" value="[]" /> reusablePeriodesField</li>
                <li><input id="isMultihoraireTabSelectedField" type="checkbox" checked="checked" /> isMultihoraireTabSelectedField</li>
                <li><input id="periodesReusingCurrentHorairesField" type="text" value="[]" /> periodesReusingCurrentHorairesField</li>
                <li><input id="daysOfTheWeekInPeriodeField" type="text" value="" /> daysOfTheWeekInPeriodeField</li>
                <li><input id="allPeriodesField" type="text" value="[]" /> allPeriodesField</li>
                <li><input id="errorsField" type="text" value="[]" /> errorsField</li>
                <li><input id="localeField" type="text" value="fr" /> localeField</li>
            </ul>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php __('Fermer') ; ?></button>
        <button type="button" class="btn btn-primary" id="horairesSubmit"><?php __('Valider') ; ?></button>
    </div>
    </div> 
</div>
</div>


<script>

function btnHoraires(event){
    var btn = event.srcElement
    let ligne = btn.closest('.row')
    console.log(ligne)
    const trIndex = jQuery(ligne).index()
    console.log(trIndex)

    let start = ligne.querySelector('input.debut').value
    let end = ligne.querySelector('input.fin').value
    if ( start == '' || end == '' ) {
        alert('<?php __('Merci de préciser les dates avant de saisir les horaires associés') ; ?>')
        return false
    }

    let input = ligne.querySelector('input.horaires')
    let description = ligne.querySelector('div.description')
    openHoraires(trIndex, start, end, input, description)
    return true
}

function openHoraires(trIndex, start, end, input, description) {

    let horaires = '' ;
    
    try {
        horaires = JSON.parse(input.value)
    } catch (e) { // On n'a pas pu désérialiser : soit la saisie était KO, soit elle était vide, on passe une valeur par défaut
        horaires = [
            {
                'id_temp' : 1,
                'type' : <?php echo json_encode($apidaeEvent->getElementReferenceByTypeAndName('HoraireType','Ouverture')) ?>,
                'timePeriods' : [
                    {
                        'id_temp' : 1,
                        'closed' : false,
                        'weekdays' : [],
                        'timeFrames' : [
                            {
                                'startTime': null,
                                'endTime' : null
                            }
                        ]
                    }
                ]
            }
        ]
    }

    let periodeOuverture = {
        dateDebut: start,
        dateFin: end,
        'horaires' : horaires
    }

    var horairesModal = document.getElementById('horairesModal') ;

    horairesModal.querySelector('#trIndex').value = trIndex
    horairesModal.querySelector('#horairesField').value = JSON.stringify(periodeOuverture)
    
    horairesModal.querySelector('#daysOfTheWeekInPeriodeField').value = JSON.stringify(joursDeLaSemaineEntre(start, end))
    horairesModal.querySelector('#daysOfTheWeekInPeriodeField').setAttribute('value',JSON.stringify(joursDeLaSemaineEntre(start, end)))
    
    multihoraire.unmountReact();
    multihoraire.mountReact({
        root:                               horairesModal.querySelector('#reactGuiContainer'),
        horairesField:                      horairesModal.querySelector('#horairesField'),
        availableHoraireTypesField:         horairesModal.querySelector('#availableHoraireTypesField'),
        reusablePeriodesField:              horairesModal.querySelector('#reusablePeriodesField'),
        isMultihoraireTabSelectedField:     horairesModal.querySelector('#isMultihoraireTabSelectedField'),
        periodesReusingCurrentHorairesField:horairesModal.querySelector('#periodesReusingCurrentHorairesField'),
        daysOfTheWeekInPeriodeField:        horairesModal.querySelector('#daysOfTheWeekInPeriodeField'),
        allPeriodesField:                   horairesModal.querySelector('#allPeriodesField'),
        errorsField:                        horairesModal.querySelector('#errorsField'),
        localeField:                        horairesModal.querySelector('#localeField')
    });
    //let nom = document.getElementById('nom').value
    var bootstrapModal = new bootstrap.Modal(horairesModal)
    bootstrapModal.show()
}

document.getElementById('horairesSubmit').addEventListener('click', function () {
    const modalElement = document.getElementById('horairesModal')
    const index = modalElement.querySelector('#trIndex').value
    
    const tbody = document.querySelector('.dates-rows');
    const ligne = tbody.querySelectorAll('.row')[index];

    console.log(index, tbody, ligne) ;

    let periodeJson = '' ;

    try {
        periodeJson = JSON.parse(document.getElementById('horairesField').getAttribute('value'))
    } catch (e) {
        alert('Une erreur s\'est produite : ' + e.message)
        return false
    }
    if (!periodeJson.hasOwnProperty("horaires") || !periodeJson.horaires.length) {
        console.warn('Retour périodes invalide');
        console.warn(periodeJson);
        return false
    }

    ligne.querySelector('input.horaires').value = JSON.stringify(periodeJson.horaires) ;
    ligne.querySelector('div.description').innerHTML = toDescription(periodeJson.horaires) ;
    
    const modalInstance = bootstrap.Modal.getInstance(modalElement)
    modalInstance.hide()
});

function joursDeLaSemaineEntre(dateDebut, dateFin) {
    const jours = new Set();
    const joursSemaine = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

    let date = new Date(dateDebut);
    const fin = new Date(dateFin);

    // Boucle jusqu'à la fin
    while (date <= fin) {
        const jourIndex = date.getDay(); // 0 (dimanche) à 6 (samedi)
        jours.add(joursSemaine[jourIndex]);
        date.setDate(date.getDate() + 1);
    }

    if ( jours.length == 7 ) {
        jours.append('HOL')
    }

    return Array.from(jours);
}

/**
 * Fournit une version affichable des horaires saisis
 */
function toDescription(horaires) {
    let description = '';
    horaires.forEach((horaire) => {
        if (horaire.hasOwnProperty('timePeriods')) {
            horaire.timePeriods.forEach((period) => {
                if (period.hasOwnProperty('timeFrames')) {
                    
                    if ( period.weekdays.length > 0 ) {
                        frWeekdays = period.weekdays.map((jour) => {
                            switch (jour) {
                                case 'MON':
                                    return 'Lundi';
                                case 'TUE':
                                    return 'Mardi';
                                case 'WED':
                                    return 'Mercredi';
                                case 'THU':
                                    return 'Jeudi';
                                case 'FRI':
                                    return 'Vendredi';
                                case 'SAT':
                                    return 'Samedi';
                                case 'SUN':
                                    return 'Dimanche';
                                default:
                                    return jour;
                            }
                        });
                        description += frWeekdays.join(', ') + ' : ';
                    }
                    description += `${period.closed ? 'Fermé' : 'Ouvert'}`;
                    period.timeFrames.forEach((timeFrame) => {
                        console.log(typeof timeFrame.startTime, timeFrame.startTime)
                        if ( 
                            typeof timeFrame.startTime != 'undefined' 
                            && timeFrame.startTime != null 
                            && timeFrame.startTime != '' 
                            && typeof timeFrame.endTime != 'undefined' 
                            && timeFrame.endTime != null 
                            && timeFrame.endTime != '' 
                        ) {
                            description += ` de ${timeFrame.startTime} à ${timeFrame.endTime} `;
                        } else if ( 
                            timeFrame.startTime != 'undefined' 
                            && timeFrame.startTime != null 
                            && timeFrame.startTime != '' 
                        ) {
                            description += ` à partir de ${timeFrame.startTime} `;
                        } else if ( 
                            timeFrame.endTime != 'undefined' 
                            && timeFrame.endTime != null 
                            && timeFrame.endTime != '' 
                        ) {
                            description += ` jusqu'à ${timeFrame.endTime} `;
                        }
                    });
                    description += '<br>';
                }
            });
        }
    });
    return description;
}

</script>