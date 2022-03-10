<?php

	namespace PierreGranger ;

    use GuzzleHttp\Command\Results;

/**
 * Extension de la classe ApidaeEvent pour l'usage d'Apidae sur event.apidae-tourisme.com
 * En raison du grand nombre d'abonnés au projet, la phase d'enregistrement était devenue trop longue
 * En particulier la phase de détermination du propriétaire
 * 
 * Cette classe ajoute différentes fonctionnalités permettant une mise en cache des informations nécessaires
 * à la déterminiation du propriétaire
 */
class ApidaeEventMM extends ApidaeEvent {

    protected ApidaeMembres $apidaeMembres ;

    public function __construct(array $params) {
        parent::__construct($params) ;
        $this->apidaeMembres = new ApidaeMembres($params) ;
    }

    /**
     * @param bool $refresh
     * @return array
     */
    public function getTerritoires(bool $refresh=false) {

        $cachekey = 'territoires' ;

        if ( $refresh === true || ( $territoires = $this->mc->get($cachekey) ) === false )
        {
            $this->debug(__METHOD__.' : mc->get failed [refresh='.$refresh.']...') ;

            $territoires = [] ;
            foreach ( $this->_config['membres'] as $m ) {
                $territoires[$m['id_territoire']] = null ;
            }

            $territoires_appels = array_chunk(array_keys($territoires),200) ;
            
            foreach ( $territoires_appels as $terrs )
            {
                $q = [
                    'identifiants' => $terrs,
                    'count' => 200,
                    'responseFields' => ['nom','id','localisation']
                 ] ;

                /** @var array|Result $result */
                $result = $this->client->rechercheListObjetsTouristiques(['query' => $q]);
                if ( ! is_array($result) && preg_match('#^Guzzle.*Result$#',get_class($result)) ) $result = $result->toArray() ;

                if ( ! isset($result['objetsTouristiques']) )
                {
                    $this->debug(__METHOD__.':'.__LINE__.' : rechercheListObjetsTouristiques failed (no objetsTouristiques key)') ;
                    return false ;
                }
                
                foreach ( $result['objetsTouristiques'] as $obt )
                {
                    //echo json_encode($obt) ;
                    $obt['perimetre'] = [] ;
                    if ( isset($obt['localisation']['perimetreGeographique']) )
                    {
                        foreach ( $obt['localisation']['perimetreGeographique'] as $com )
                        $obt['perimetre'][$com['code']] = $com ;
                    }
                    $territoires[$obt['id']] = $obt ;
                }

            }

            $this->debug(__METHOD__.' : mc->set...[expiration='.$this->mc_expiration.']') ;
            $this->mc->set($cachekey,$territoires,$this->mc_expiration) ;
        }

        return $territoires ;
    }

    /**
     * @param string $codeInsee SUPER IMPORTANT d'être en string, pour les codes INSEE avec un 0 devant !
     * @return array Liste des membres, avec leur identifiant comme clé
     */
    public function getMembresFromCommuneInsee(string $codeInsee, bool $refresh = false) {

        $cachekey = 'membresCommune'.$codeInsee ;

        if ( $refresh === true || ( $membres = $this->mc->get($cachekey) ) === false )
        {
            $this->debug(__METHOD__.' : mc->get failed [refresh='.$refresh.']...') ;

            try {
                $membresCommune = $this->apidaeMembres->getMembres(
                    ['communeCode'=>$codeInsee] // communeCode = code INSEE
                ) ;
            } catch ( \Exception $e ) {
                $this->debug(__METHOD__.':'.__LINE__.' : apidaeMembres->getMembres failed') ;
                return false ;
            }

            echo json_encode($membresCommune) ;

            if ( ! is_array($membresCommune) )
            {
                $this->debug(__METHOD__.':'.__LINE__.' : apidaeMembres->getMembres failed') ;
                return false ;
            }

            $membres = [] ;
            foreach ( $membresCommune as $mb )
            {
                echo $mb['id']. '-' ;
                $membres[$mb['id']] = $mb ;
            }

            $this->debug(__METHOD__.' : mc->set...[expiration='.$this->mc_expiration.']') ;
            $this->mc->set($cachekey,$membres,$this->mc_expiration) ;
        }

        return $membres ;
    }

}