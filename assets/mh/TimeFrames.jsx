import React from 'react'
import TimeFrame from './TimeFrame'

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

export default TimeFrames