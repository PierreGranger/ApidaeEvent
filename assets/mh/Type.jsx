import React from 'react'
import { types } from '../mh'

function Type({onChange}) {

    return (
        <select name="type" className="form-select" onChange={event => onChange(event.target.value)}>
                { types.map(type => (
                    <option key={type.reference} value={type.reference}>{type.description}</option>
                ))}
        </select>
    )
}

export default Type