
import React from 'react'

import { types } from '../mh'

/**
 * 
 * @param {typeRef} référence du type (opening, departure...)
 * @param {cle} clé d'identification pour permettre la suppression du bon TimeFrame par TimeFrames.removeTimeFrame
 * @param {removeTimeFrame} fonction TimeFrame.removeTimeFrame déclenchée au clic sur le bouton de supp. d'un TimeFrame
 */
 function TimeFrame({typeRef, cle, removeTimeFrame, changeTimeFrame}) {

    /**
     * typeRef étant un string, on récupère le type complet associé (objet) dans types
     */
    const type = types.find(t => t.reference == typeRef)

    const [times, setTimes] = React.useState({
        startTime:'',
        endTime:'',
        recurrence:''
    })

    let nbCol = 1
    if ( ! type.isSingleTime ) nbCol++
    if ( type.isRecurring ) nbCol++
    let sizeCol = 12 / nbCol

    function changeTime(k,v) {
        //setTimes(filterTime({...times, [k] : v.target.value}))
        setTimes({...times, [k] : v.target.value})
    }

    // function filterTime(timesToFilter) {
    //     let clone = JSON.parse(JSON.stringify(timesToFilter))

    //     if ( type.isSingleTime ) {
    //         if ( clone.endTime != '' ) clone.endTime = ''
    //     }
    //     else if ( type.isSingleTime || ! type.isRecurring ) {
    //         if ( clone.recurrence != '' ) clone.recurrence = ''
    //     }

    //     return clone
    // }

    React.useEffect(() => {
        let ret = {
            index:cle,
            startTime:times.startTime
        }
        if ( ! type.isSingleTime && times.endTime != '' ) ret.endTime = times.endTime
        if ( type.isRecurring && times.recurrence != '' ) ret.recurrence = times.recurrence

        changeTimeFrame(ret)
    },[times, typeRef])

    return (
        <div className="timeFrame row">
            <div className="col-1">
                <i className="fa-solid fa-circle-minus" onClick={event => removeTimeFrame(cle)}></i>
            </div>
            <div className="col-11 row">
                <div className={`form-floating mb-3 col-${sizeCol}`}>
                    <input type="time" className="form-control" name="timeFrame[{cle}][startTime]" value={times.startTime} onChange={event => changeTime('startTime',event)} />
                    <label>{! type.isSingleTime ? 'de' : 'à' }</label>
                </div>
                {
                    ! type.isSingleTime ?
                    <div className={`form-floating mb-3 col-${sizeCol}`}>
                        <input type="time" className="form-control" name="timeFrame[{cle}][endTime]" value={times.endTime} onChange={event => changeTime('endTime',event)} />
                        <label>à</label>
                    </div> : ''
                }
                {
                    type.isRecurring ?
                    <div className={`form-floating mb-3 col-${sizeCol}`}>
                        <input type="time" className="form-control" name="timeFrame[{cle}][recurrence]" value={times.recurrence} onChange={event => changeTime('recurrence',event)} />
                        <label>Toutes les</label>
                    </div> : ''
                }
            </div>
        </div>
    )
}

export default TimeFrame