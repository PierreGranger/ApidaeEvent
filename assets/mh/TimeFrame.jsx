
import React from 'react'

import { types } from '../mh'

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

export default TimeFrame