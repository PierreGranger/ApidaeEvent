'use strict' ;

import React from 'react'
import ReactDOM from 'react-dom/client'
import moment from 'moment'

const listDays = [
    { index:1, key:'MON', lib:'Lundi'},
    { index:2, key:'TUE', lib:'Mardi'},
    { index:3, key:'WED', lib:'Mercredi'},
    { index:4, key:'THU', lib:'Jeudi'},
    { index:5, key:'FRI', lib:'Vendredi'},
    { index:6, key:'SAT', lib:'Samedi'},
    { index:0, key:'SUN', lib:'Dimanche'}
]

/**
 * @todo récupérer à partir de https://api.apihours.apidae-tourisme.com/labels (une fois qu'on aura les types objets dedans)
 */
const types = [
    { reference : 'opening',        isRecurring : false,    isSingleTime : false,   description : 'Ouverture' },
    { reference : 'last_entry',     isRecurring : false,    isSingleTime : true,    description : 'Dernière entrée' },
    { reference : 'ceremony',       isRecurring : false,    isSingleTime : false,   description : 'Horaires de cérémonie' },
    { reference : 'guided_tour',    isRecurring : true,     isSingleTime : false,   description : 'Horaires de visite guidée' },
    { reference : 'departure',      isRecurring : true,     isSingleTime : false,   description : 'Horaires de départ' },
    { reference : 'representation', isRecurring : false,    isSingleTime : false,   description : 'Horaires de représentation' }
]

function Multihoraire() {

    const [periodes, setPeriodes] = React.useState([0])

    function addPeriode() {
        setPeriodes([...periodes,periodes.length>0?Math.max(...periodes)+1:0])
    }

    function removePeriode(item) {
        setPeriodes(periodes.filter(i => i !== item))
    }

    return (
        <div>
            {periodes.map(item => (
                <Periode key={item} cle={item} removePeriode={removePeriode}></Periode>
            ))}
            <a className="btn btn-primary" onClick={addPeriode}>Ajouter des dates</a>
        </div>
    )
}

function Periode({removePeriode, cle}) {

    const [timePeriods, setTimePeriods] = React.useState([0])
    const [dates, setDates] = React.useState({
        debut:null,
        debutMin:new Date().toISOString().slice(0, 10),
        debutMax:null,
        fin:null,
        finMin:new Date().toISOString().slice(0, 10),
        finMax:null
    })
    const [days, setDays] = React.useState([])
    const [showDateFin, setShowDateFin] = React.useState(true)

    function addTimePeriod() {
        setTimePeriods([...timePeriods,timePeriods.length>0?Math.max(...timePeriods)+1:0])
    }

    function removeTimePeriod(item) {
        setTimePeriods(timePeriods.filter(i => i !== item))
    }

    function dateChange(type, event) {
        if ( type == 'debut') {
            let d = event.target.value.split('-')
            let newD = new Date(d[0],d[1]-1,d[2])
            setDates({...dates,debut:newD,finMin:event.target.value})
        } else {
            let d = event.target.value.split('-')
            let newD = new Date(d[0],d[1]-1,d[2])
            setDates({...dates,fin:newD})
        }
    }

    function changeOneDay(event) {
        setShowDateFin(!event.target.checked)
    }

    React.useEffect(() => {
        let newDays = []

        // Date début seulement
        if ( dates.debut != null && ( dates.fin == null || ! showDateFin || dates.fin == dates.debut ) ) {
            listDays.map(function(ad) {
                if ( ad.index == dates.debut.getDay() ) newDays.push(ad)
            })
        }
        // Période entre 2 dates
        else if ( dates.debut != null && dates.fin != null && dates.fin > dates.debut ) {
            var diff = Math.floor((dates.fin.getTime()-dates.debut.getTime())/(24*3600*1000));
            // + de 7 jours : on affiche tous les jours
            if ( diff >= 7 ) newDays = [...listDays]
            // - de 7 jours : on affiche seulement les jours concernés
            else if ( diff >= 2 ) {
                for ( let i = 0 ; i <= diff ; i++ ) {
                    let d = new Date()
                    d.setDate(dates.debut.getDate() + i)
                    console.log(d)
                    newDays = [...newDays,...listDays.filter(i => i.index == d.getDay())]
                }
            }
        }

        setDays(newDays)
    },[dates])

    let dateFinClass = ''
    let dateFinFeedback = ''
    // let dateDebutMin = moment(dates.debutMin,'YYYY-DD-YY') ;
    // let dateFinMin = moment(dates.finMin,'YYYY-DD-YY') ;
    //dateFinClass = dateDebutMin.diff(dateFinMin) > 0 ? 'is-invalid' : ''
    if ( dates.fin != null && dates.debut > dates.fin ) {
        dateFinClass = dates.debut > dates.fin ? 'is-invalid' : ''
        dateFinFeedback = <div className="invalid-feedback">Date de fin invalide</div>
    }

    return (
        <div className="periode row">
            <div className="col-3 row">
                <div className="col-4">
                    <i className="fa-solid fa-circle-minus" onClick={event => removePeriode(cle)}></i>
                    <div className="form-check">
                        <input type="checkbox" onChange={changeOneDay} className="form-check-input" />
                        <label className="form-check-label" htmlFor="changeOneDay">Un seul jour</label>
                    </div>
                </div>
                <div className="dates col-8">
                    <div className="form-floating mb-3">
                        <input id="du" type="date" className="form-control" 
                            min={dates.debutMin}
                            max={dates.debutMax}
                            onChange={(e) => dateChange('debut',e)} />
                        <label htmlFor="de">{showDateFin ? 'Du' : 'Le' }</label>
                    </div>
                    { showDateFin ?
                        <div className="form-floating mb-3">
                            <input id="au" type="date" className={`form-control ${dateFinClass}`}
                                min={dates.finMin}
                                max={dates.finMax}
                                onChange={(e) => dateChange('fin',e)} />
                            <label htmlFor="TODO">Au</label>
                            {dateFinFeedback}
                        </div> : ''
                    }
                </div>
                <div className="form-floating">
                    <textarea className="form-control" placeholder="Autres précisions" id="TODO"></textarea>
                    <label className="floatingTextarea" htmlFor="TODO">Autres précisions</label>
                </div>
            </div>
            <div className="timePeriods col-9">
                {timePeriods.map(item => (
                    <TimePeriod key={item} cle={item} removeTimePeriod={removeTimePeriod} days={days}></TimePeriod>
                ))}
                <a className="btn btn-primary" onClick={addTimePeriod}>Ajouter des horaires</a>
            </div>
        </div>
    )
}

function TimePeriod({cle, removeTimePeriod, days}) {

    const [type, setType] = React.useState('opening')

    return (
        <div className="timePeriod row">
            <div className="col col-1">
                <i className="fa-solid fa-circle-minus" onClick={event => removeTimePeriod(cle)}></i>
            </div>
            <div className="col">
                <Type type={type} onChange={setType}></Type>
                <WeekDays allowedDays={days}></WeekDays>
            </div>
            <div className="col">
                <TimeFrames type={type}></TimeFrames>
            </div>
        </div>
    )
}

function Type({onChange}) {

    return (
        <select name="type" className="form-select" onChange={event => onChange(event.target.value)}>
                { types.map(type => (
                    <option key={type.reference} value={type.reference}>{type.description}</option>
                ))}
        </select>
    )
}

function WeekDays({allowedDays}) {
    if ( allowedDays.length > 1 ) {
    return (
        <div className="weekdays">
            {allowedDays.map(day => (
                <span key={day.key}>
                    <input type="checkbox" className="btn-check" id={day.key} autoComplete="off" />
                    <label className="btn btn-secondary" htmlFor={day.key}>{day.lib}</label>
                </span>
            ))}
        </div>
    )
    } else return '' ;
}

function TimeFrames({type}) {

    const [timeFrames, setTimeFrames] = React.useState([0])

    function addTimeFrame() {
        setTimeFrames([...timeFrames,timeFrames.length>0?Math.max(...timeFrames)+1:0])
    }

    function removeTimeFrame(item) {
        setTimeFrames(timeFrames.filter(i => i !== item))
    }

    return (
        <div className="timeFrames">
            {timeFrames.map(item => (
                <TimeFrame typeRef={type} key={item} cle={item} removeTimeFrame={removeTimeFrame}></TimeFrame>
            ))}
            <a className="btn btn-primary" onClick={addTimeFrame}>Ajouter une plage horaire</a>
        </div>
    )
}

/**
 * 
 * @param {typeRef} référence du type (opening, departure...)
 * @param {cle} clé d'identification pour permettre la suppression du bon TimeFrame par TimeFrames.removeTimeFrame
 * @param {removeTimeFrame} fonction TimeFrame.removeTimeFrame déclenchée au clic sur le bouton de supp. d'un TimeFrame
 */
function TimeFrame({typeRef, cle, removeTimeFrame}) {

    /**
     * typeRef étant un string, on récupère le type complet associé (objet) dans types
     */
    const type = types.find(t => t.reference == typeRef)

    let nbCol = 1
    if ( ! type.isSingleTime ) nbCol++
    if ( type.isRecurring ) nbCol++
    let sizeCol = 12 / nbCol

    return (
        <div className="timeFrame row">
            <div className="col-1">
                <i className="fa-solid fa-circle-minus" onClick={event => removeTimeFrame(cle)}></i>
            </div>
            <div className="col-11 row">
                <div className={`form-floating mb-3 col-${sizeCol}`}>
                    <input type="time" className="form-control" name="timeFrame[{cle}][startTime]" id="startTime_{cle}" />
                    <label htmlFor="startTime_{cle}">{! type.isSingleTime ? 'de' : 'à' }</label>
                </div>
                {
                    ! type.isSingleTime ?
                    <div className={`form-floating mb-3 col-${sizeCol}`}>
                        <input type="time" className="form-control" name="timeFrame[{cle}][endTime]" id="endTime_{cle}" />
                        <label htmlFor="endTime_{cle}">à</label>
                    </div> : ''
                }
                {
                    type.isRecurring ?
                    <div className={`form-floating mb-3 col-${sizeCol}`}>
                        <input type="time" className="form-control" name="timeFrame[{cle}][recurrence]" id="recurrence_{cle}" />
                        <label htmlFor="recurrence_{cle}">Toutes les</label>
                    </div> : ''
                }
            </div>
        </div>
    )
}

const container = document.getElementById('multihoraire')
if ( container != null )
{
    const multihoraire = ReactDOM.createRoot(container)
    multihoraire.render(<Multihoraire />)
}