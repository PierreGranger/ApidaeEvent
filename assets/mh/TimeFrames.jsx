import React from 'react'
import TimeFrame from './TimeFrame'

function TimeFrames({type, changeTimeFrames}) {

    const [timeFrames, setTimeFrames] = React.useState([{index:0}])

    function addTimeFrame() {
        setTimeFrames([...timeFrames,{index:timeFrames.length>0?Math.max(...timeFrames.map(i => i.index))+1:0}])
    }

    function removeTimeFrame(item) {
        setTimeFrames(timeFrames.filter(i => i.index !== item))
    }

    function changeTimeFrame(timeFrame) {
        let newTimeFrames = []
        let found = false
        timeFrames.map(function(item) {
            if ( item.index == timeFrame.index ) {
                found = true
                newTimeFrames.push(timeFrame)
            } else {
                newTimeFrames.push(item)
            }
        })
        if ( ! found ) {
            newTimeFrames.push(timeFrame)
        }
        console.log('changeTimeFrame','newTimeFrames',newTimeFrames)
        setTimeFrames(newTimeFrames)
    }

    React.useEffect(() => {
        changeTimeFrames(timeFrames)
    },[timeFrames])

    return (
        <div className="timeFrames">
            {timeFrames.map(item => (
                <TimeFrame typeRef={type} key={item.index} cle={item.index} removeTimeFrame={removeTimeFrame} changeTimeFrame={changeTimeFrame}></TimeFrame>
            ))}
            <a className="btn btn-primary" onClick={addTimeFrame}>Ajouter une plage horaire</a>
        </div>
    )
}

export default TimeFrames