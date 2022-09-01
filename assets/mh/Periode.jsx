import React from 'react'
import TimePeriod from './TimePeriod'

import { listDays, convertDate } from '../mh'

function Periode({removePeriode, changePeriode, cle}) {

    const [timePeriods, setTimePeriods] = React.useState([{index:0}])
    const [dates, setDates] = React.useState({
        debut:null,
        debutMin:convertDate(new Date()),
        debutMax:null,
        fin:null,
        finMin:convertDate(new Date()),
        finMax:null
    })
    const [allowedDays, setAllowedDays] = React.useState([])
    const [showDateFin, setShowDateFin] = React.useState(true)

    function addTimePeriod() {
        setTimePeriods([...timePeriods,{index:timePeriods.length>0?Math.max(...timePeriods.map(i => i.index))+1:0}])
    }

    function removeTimePeriod(item) {
        setTimePeriods(timePeriods.filter(i => i.index !== item))
    }

    function changeTimePeriod(changedPeriod) {
        let newTimePeriods = []
        let found = false ;
        timePeriods.map(function (tp) {
            if ( tp.index == changedPeriod.index ) {
                found = true ;
                newTimePeriods.push(changedPeriod)
            } else newTimePeriods.push(tp)
        })
        if ( ! found ) newTimePeriods.push(changedPeriod)
        setTimePeriods(newTimePeriods)
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
                    newDays = [...newDays,...listDays.filter(i => i.index == d.getDay())]
                }
            }
        }

        setAllowedDays(newDays)
        if ( dates.debut != null )
        {
            let changedPeriod = {
                index : cle,
                dateDebut : convertDate(dates.debut)
            }
            if ( dates.fin != null ) changedPeriod.dateFin = convertDate(dates.fin)
            changedPeriod.timePeriods = timePeriods
            changePeriode(changedPeriod)
        }
    },[dates,timePeriods])

    let dateFinClass = ''
    let dateFinFeedback = ''

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
                    <TimePeriod key={item.index} cle={item.index} removeTimePeriod={removeTimePeriod} changeTimePeriod={changeTimePeriod} allowedDays={allowedDays} periodeCle={cle}></TimePeriod>
                ))}
                <a className="btn btn-primary" onClick={addTimePeriod}>Ajouter des horaires</a>
            </div>
        </div>
    )
}

export default Periode