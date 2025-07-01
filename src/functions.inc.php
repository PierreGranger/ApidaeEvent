<?php

function __(string $msg, bool $echo=true) {
    if ( ! $echo ) return _($msg) ;
    echo _($msg) ;
}

/**
 * Nettoye (par référence) des données horaires pour l'envoi par API :
 * - on retire les id, id_temp pour les données classiques (timeFrames...)
 * - on ne conserve que id et elementReferenceType pour les données elementReference
 */
function cleanHoraires(&$data) {

    $clean = ['id_temp','id'] ;
    $keep = [] ;

    if ( is_object($data) ) {
        if ( isset($data->elementReferenceType) ) {
            $keep = ['elementReferenceType', 'id'] ;
        }

        foreach ($data as $key => $value) {

            if (
                ( sizeof($keep) > 0 && ! in_array($key, $keep) ) // elementReference
                || ( sizeof($keep) == 0 && in_array($key, $clean) ) // tout le reste
            ) {
                unset($data->$key);
            } else {
                cleanHoraires($data->$key);
            }

            if ( $key == 'timeFrames' && is_array($value) ) {
                foreach ( $value as $key_TimeFrame => $timeFrame ) { // $value = [{"startTime":"11:00"},{"endTime":"12:00"}]
                    foreach ( $timeFrame as $key_TimeFrameData => $timeFrameData ) { // $key_TimeFrameData = startTime, endTime, recurrence
                        if ( $timeFrameData == '' || $timeFrameData == null ) {
                            unset($data->timeFrames[$key_TimeFrame]->$key_TimeFrameData) ;
                        }
                    }
                    if ( sizeof((array)$data->timeFrames[$key_TimeFrame]) == 0 ) {
                        unset($data->timeFrames[$key_TimeFrame]) ;
                    }
                }
            }

        }
    } elseif ( is_array($data) ) {
        foreach ( $data as $key => $value ) {
            if ( in_array($key, $clean) ) {
                unset($data[$key]) ;
            } else {
                cleanHoraires($data[$key]) ;
            }
        }
    }
}
