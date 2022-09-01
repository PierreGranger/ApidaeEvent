import React from 'react'

function WeekDays({allowedDays, changeWeekDays, periodeCle, timePeriodCle}) {

    const [days, setDays] = React.useState([])

    function changeDay(event) {
        let newDays = [...days]
        if ( event.target.checked ) {
            if ( ! days.find(d => d == event.target.value) ) newDays.push(event.target.value)
        }
        else {
            newDays = days.filter(d => d == event.target.value)
        }
        setDays(newDays)
    }

    React.useEffect(() => {
        changeWeekDays(days)
    },[days])

    if ( allowedDays.length > 1 ) {
    return (
        <div className="weekdays">
            {allowedDays.map(allowedDay => (
                <span key={allowedDay.key}>
                    <input type="checkbox" className="btn-check" autoComplete="off" 
                        value={allowedDay.key}
                        id={`weekday_${periodeCle}_${timePeriodCle}_${allowedDay.key}`}
                        onChange={changeDay} 
                        defaultChecked={days.find(d => d == allowedDay.key )}
                    />
                    <label
                        className="btn btn-outline-success"
                        htmlFor={`weekday_${periodeCle}_${timePeriodCle}_${allowedDay.key}`}>
                            {allowedDay.lib}
                    </label>
                </span>
            ))}
        </div>
    )
    } else return '' ;
}

export default WeekDays