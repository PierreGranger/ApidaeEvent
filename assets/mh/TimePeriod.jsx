import React from 'react'

import Type from './Type'
import WeekDays from './WeekDays'
import TimeFrames from './TimeFrames'

function TimePeriod({cle, removeTimePeriod, changeTimePeriod, allowedDays, periodeCle}) {

    const [type, setType] = React.useState('opening')
    const [weekDays, setWeekDays] = React.useState([])
    const [timeFrames, setTimeFrames] = React.useState([{index:0}])

    function changeTimeFrames(timeFrames) {
        setTimeFrames(timeFrames)
    }

    React.useEffect(() => {
        changeTimePeriod({
            index:cle,
            type:type,
            weekDays:weekDays,
            timeFrames:timeFrames
        })
    },[type,weekDays,timeFrames])

    return (
        <div className="timePeriod row">
            <div className="col col-1">
                <i className="fa-solid fa-circle-minus" onClick={event => removeTimePeriod(cle)}></i>
            </div>
            <div className="col">
                <Type type={type} onChange={setType}></Type>
                <WeekDays allowedDays={allowedDays} changeWeekDays={setWeekDays} periodeCle={periodeCle} timePeriodCle={cle}></WeekDays>
            </div>
            <div className="col">
                <TimeFrames type={type} changeTimeFrames={changeTimeFrames}></TimeFrames>
            </div>
        </div>
    )
}

export default TimePeriod