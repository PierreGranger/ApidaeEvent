'use strict' ;

import React from 'react'
import ReactDOM from 'react-dom/client'
import Periode from './mh/Periode'

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
 * @todo récupérer à partir de https://api.apihours.apidae-tourisme.com/config
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

    const [periodes, setPeriodes] = React.useState([{index:0}])
    const [json, setJson] = React.useState()

    function addPeriode() {
        setPeriodes([...periodes,{index:periodes.length>0?Math.max(...periodes.map(p => p.index))+1:0}])
    }

    function removePeriode(item) {
        setPeriodes(periodes.filter(i => i.index !== item))
    }

    function changePeriode(periode) {
        // On cherche la période dans periodesValues
        let newPeriodes = []
        let found = false
        periodes.map(function(p){
            if ( p.index == periode.index ) {
                found = true
                newPeriodes.push(periode)
            } else newPeriodes.push(p)
        })
        if ( ! found )
            newPeriodes.push(periode)
        setPeriodes(newPeriodes)
    }

    React.useEffect(() => {
        setJson(JSON.stringify(cleanIndex(periodes), null, "\t"))
    },[periodes])

    return (
        <div>
            {periodes.map(item => (
                <Periode key={item.index} cle={item.index} removePeriode={removePeriode} changePeriode={changePeriode}></Periode>
            ))}
            <a className="btn btn-primary" onClick={addPeriode}>Ajouter des dates</a>
            <pre>{json}</pre>
        </div>
    )
}

function convertDate(date) {
    var yyyy = date.getFullYear().toString();
    var mm = (date.getMonth()+1).toString();
    var dd  = date.getDate().toString();

    var mmChars = mm.split('');
    var ddChars = dd.split('');

    return yyyy + '-' + (mmChars[1]?mm:"0"+mmChars[0]) + '-' + (ddChars[1]?dd:"0"+ddChars[0]);
}

function cleanIndex(object) {
    //let clone = [...object]
    //let clone = Object.assign({},object)
    let clone = JSON.parse(JSON.stringify(object));

    //clone = clone.filter(o => ( o && Object.keys(o).length === 0 && Object.getPrototypeOf(o) == Object.prototype ))

    clone.map(function(o) {
        delete o.index
        for ( const [k, v] of Object.entries(o) ) {
            if ( Array.isArray(v) ) o[k] = cleanIndex(v)
        }
    })
    return clone
}

const container = document.getElementById('multihoraire')
if ( container != null )
{
    const multihoraire = ReactDOM.createRoot(container)
    multihoraire.render(<Multihoraire />)
}

export { listDays }
export { types }
export { convertDate }