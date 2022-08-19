'use strict' ;

function Multihoraire() {

    const [periodes, setPeriodes] = React.useState([0])

    function addPeriode() {
        setPeriodes([...periodes,periodes.length>0?Math.max(...periodes)+1:0])
    }

    function removePeriode(item) {
        setPeriodes(periodes.filter(i => i !== item))
    }

    return (
        <div className="table-responsive">
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
        dateDebutMin:new Date().toISOString().slice(0, 10),
        dateDebutMax:null,
        dateFinMin:new Date().toISOString().slice(0, 10),
        dateFinMax:null
    })

    function addTimePeriod() {
        setTimePeriods([...timePeriods,timePeriods.length>0?Math.max(...timePeriods)+1:0])
    }

    function removeTimePeriod(item) {
        setTimePeriods(timePeriods.filter(i => i !== item))
    }

    function dateChange(type, event) {
        if ( type == 'debut') setDates({...dates,dateFinMin:event.target.value})
    }

    const current = new Date()
    const now = `${current.getFullYear()}-${current.getMonth()+1}-${current.getDate()}`;

    let dateFinClass = ''
    let dateDebutMin = moment(dates.dateDebutMin,'YYYY-DD-YY') ;
    let dateFinMin = moment(dates.dateFinMin,'YYYY-DD-YY') ;
    dateFinClass = dateDebutMin.diff(dateFinMin) > 0 ? 'is-invalid' : ''

    return (
        <div className="periode row">
            <div className="col-1">
                {cle}<i className="fa-solid fa-circle-minus" onClick={event => removePeriode(cle)}></i>
                {new Date().toISOString()}
            </div>
            <div className="dates col-2">
                <div className="form-floating mb-3">
                    <input id="du" type="date" className="form-control" 
                        min={dates.dateDebutMin}
                        max={dates.dateDebutMax}
                        onChange={(e) => dateChange('debut',e)} />
                    <label htmlFor="de">Du</label>
                </div>
                <div className="form-floating mb-3">
                    <input id="au" type="date" className={`form-control ${dateFinClass}`}
                        min={dates.dateFinMin}
                        max={dates.dateFinMax}
                        onChange={(e) => dateChange('fin',e)} />
                    <label htmlFor="au">Au</label>
                </div>
            </div>
            <div className="timePeriods col-9">
                {timePeriods.map(item => (
                    <TimePeriod key={item} cle={item}></TimePeriod>
                ))}
            </div>
                <a className="btn btn-primary" onClick={addTimePeriod}>Ajouter des horaires</a>
                {timePeriods.length}
        </div>
    )
}

function TimePeriod({cle}) {

    return (
        <div className="timePeriod row">
            <Type></Type>
            <WeekDays></WeekDays>
            <TimeFrames></TimeFrames>
        </div>
    )
}

function Type() {
    return (
        <div className="type col-3">
            <select name="type" className="form-select">
                <option value="opening">Ouverture</option>
                <option value="last_entry">Dernière entrée</option>
                <option value="ceremony">Horaires de cérémonie</option>
                <option value="guided_tour">Horaires de visite guidée</option>
                <option value="departure">Horaires de départ</option>
                <option value="representation">Horaires de représentation</option>
            </select>
        </div>
    )
}

function WeekDays() {
    const days = [
        { key:'MON', lib:'Lundi'},
        { key:'TUE', lib:'Mardi'},
        { key:'WED', lib:'Mercredi'},
        { key:'THU', lib:'Jeudi'},
        { key:'FRI', lib:'Vendredi'},
        { key:'SAT', lib:'Samedi'},
        { key:'SUN', lib:'Dimanche'}
    ]
    
    return (
        <div className="weekdays col-3">
            {days.map(day => (
                <span key={day.key}>
                    <input type="checkbox" className="btn-check" id={day.key} autoComplete="off" />
                    <label className="btn btn-secondary" htmlFor={day.key}>{day.lib}</label>
                </span>
            ))}
        </div>
    )
}

function TimeFrames() {

    const [timeFrames, setTimeFrames] = React.useState([0])

    function addTimeFrame() {
        setTimeFrames([...timeFrames,timeFrames.length>0?Math.max(...timeFrames)+1:0])
    }

    function removeTimeFrame() {

    }

    return (
        <div className="timeFrames col-6">
            {timeFrames.map(item => (
                <TimeFrame key={item} cle={item} removeTimeFrame={removeTimeFrame}></TimeFrame>
            ))}
            <a className="btn btn-primary" onClick={addTimeFrame}>Ajouter une plage horaire</a>
        </div>
    )
}

function TimeFrame(removeTimeFrame) {

    return (
        <div className="timeFrame row">
            <div className="form-floating mb-3 col">
                <input type="time" className="form-control" name="de" id="de" />
                <label htmlFor="de">de</label>
            </div>
            <div className="form-floating mb-3 col">
                <input type="time" className="form-control" name="a" id="a" />
                <label htmlFor="a">à</label>
            </div>
            <i className="fa-solid fa-circle-minus" onclick={removeTimeFrame}></i>
        </div>
    )
}

const container = document.getElementById('multihoraire')
const multihoraire = ReactDOM.createRoot(container)
multihoraire.render(<Multihoraire />)